<?php

	/**
	 * Telnet class
	 *
	 * Used to execute remote commands via telnet connection
	 * Usess sockets functions and fgetc() to process result
	 *
	 * All methods throw Exceptions on error
	 *
	 * Written by Dalibor Andzakovic <dali@swerve.co.nz>
	 * Based on the code originally written by Marc Ennaji and extended by
	 * Matthias Blaser <mb@adfinis.ch>
	 *
	 * Extended by Christian Hammers <chammers@netcologne.de>
	 *
	 * Modified by Tobias Schramm <tobias@t-sys.eu>
	 * Modified by Steven Tappert <admin@steven-tappert.de>
	 */
	class Telnet
	{
		private $host;
		private $port;
		private $timeout;
		private $timeoutSec;
		private $timeoutUsec;

		private $socket = NULL;
		private $buffer = NULL;
		private $promptstr;
		private $errno;
		private $errstr;
		private $strip_promptstr = TRUE;

		//telnet control characters
		private $NULL;
		private $DC1;
		private $WILL;
		private $WONT;
		private $DO;
		private $DONT;
		private $IAC;

		private $global_buffer = '';

		/**
		 * The constructor. Initialises all connection parameters
		 * defaults to localhost port 23 (standard telnet port)
		 * and '>' as prompt
		 *
		 * @param string     $host      Host name or IP address (IPv4 and IPv6 supported!)
		 * @param int|string $port      port number
		 * @param string     $promptstr Telnet prompt string
		 * @param int        $timeout   Connection timeout in seconds
		 * @param int        $stream_timeout
		 *
		 * @internal param float $streamTimeout Stream timeout in decimal seconds
		 * @return \Telnet
		 */
		public function __construct($host = '127.0.0.1', $port = '23', $promptstr = '>', $timeout = 10, $stream_timeout = 1)
		{
			$this->host = $host;
			$this->port = $port;
			$this->timeout = $timeout;
			$this->setPromptstr($promptstr);
			$this->setStreamTimeout($stream_timeout);

			$this->DC1 = chr(17);
			$this->WILL = chr(251);
			$this->WONT = chr(252);
			$this->DO = chr(253);
			$this->DONT = chr(254);
			$this->IAC = chr(255);
			$this->NULL = chr(0);

			$this->connect();
		}

		/**
		 * Destructor. Closes connection and frees buffers
		 *
		 * @return void
		 */
		public function __destruct()
		{
			$this->disconnect();
			$this->buffer = NULL;
			$this->global_buffer = NULL;
		}

		/**
		 * Tries to connect to remote host. Returns true on success.
		 *
		 * @throws TelnetException
		 * @return boolean
		 */
		public function connect()
		{
			if (!preg_match('/([0-9]{1,3}\\.){3,3}[0-9]{1,3}/', $this->host))
			{
				if (preg_match('/^(((?=(?>.*?(::))(?!.+\3)))\3?|([\dA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|(25[0-5]|(2[0-4]|1\d|[1-9])?\d)(\.(?7)){3})\z/i', $this->host))
				{
					$this->host = "[{$this->host}]";
				}
				else
				{
					$ipaddr = gethostbyname($this->host);

					if ($this->host == $ipaddr)
					{
						throw new TelnetException( "Can't resolve hostname: {$this->host}" );
					}
					else
					{
						$this->host = $ipaddr;
					}
				}
			}
			$this->socket = @fsockopen($this->host, $this->port, $this->errno, $this->errstr, $this->timeout);
			if (!$this->socket)
			{
				throw new TelnetException( "Can't connect to {$this->host} on port {$this->port}" );
			}

			if (!empty( $this->promptstr )) //Wait for the first prompt to appear to make sure server is ready
			{
				$this->waitPromptstr();
			}

			return TRUE;
		}

		/**
		 * Closes the socket if open
		 *
		 * @throws TelnetException
		 * @return void
		 */
		public function disconnect()
		{
			if ($this->socket)
			{
				if (!fclose($this->socket))
				{
					throw new TelnetException( "Failed closing telnet-socket" );
				}

				$this->socket = NULL;
			}
		}

		/**
		 * Executes a command and returns a string with the result.
		 * Strips last newline if stripPromptstr is set to true
		 *
		 * @param string  $command Command to execute
		 * @param boolean $newline Default TRUE, adds newline after the command
		 *
		 * @return string Command result
		 */
		public function exec($command, $newline = TRUE)
		{
			$this->write($command, $newline);
			$this->waitPromptstr();
			return $this->getBuffer();
		}

		/**
		 * Sets the string of characters to respond to.
		 * This should be set to the last character of the command line promptstr
		 *
		 * @param string $str String to respond to, defaults to '>'
		 *
		 * @return void
		 */
		public function setPromptstr($str = '>')
		{
			$this->promptstr = preg_quote($str, '/');
		}

		/**
		 * Returns the content of the global buffer
		 *
		 * @return string Content of the global buffer
		 */
		public function getGlobalBuffer()
		{
			return $this->global_buffer;
		}

		/**
		 * Clears the buffer
		 *
		 * @return void
		 */
		public function clearBuffer()
		{
			$this->buffer = '';
		}

		/**
		 * Sets the stream timeout.
		 *
		 * @param float $timeout
		 *
		 * @return void
		 */
		public function setStreamTimeout($timeout)
		{
			$this->timeoutUsec = (int)( fmod($timeout, 1) * 1000000 );
			$this->timeoutSec = (int)$timeout;
		}

		/**
		 * Set if the prompt string should be stripped from the buffer after reading.
		 *
		 * @param $strip boolean if the promptstr should be stripped.
		 *
		 * @return void
		 */
		public function stripPromptstrFromBuffer($strip)
		{
			$this->strip_promptstr = $strip;
		}

		/**
		 * Gets characters from the socket
		 *
		 * @return String
		 */
		protected function getc()
		{
			stream_set_timeout($this->socket, $this->timeoutSec, $this->timeoutUsec);
			$char = fgetc($this->socket);
			$this->global_buffer .= $char;
			return $char;
		}

		/**
		 * Reads data from the socket and adds them to the command buffer.
		 * Handles telnet control characters. Stops when $promptstr is ecountered.
		 *
		 * @param string $promptstr
		 *
		 * @throws TelnetException
		 * @return boolean
		 */
		protected function readTo($promptstr)
		{
			if (!$this->socket)
			{
				throw new TelnetException( "Telnet connection closed" );
			}

			$this->clearBuffer(); //clear buffer
			$until_t = time() + $this->timeout;
			do
			{
				if (time() > $until_t) //crash if timeout exceeded
				{
					throw new TelnetException( "Couldn't find the requested prompt string '$promptstr' within {$this->timeout} seconds" );
				}

				$char = $this->getc();
				if ($char === FALSE)
				{
					if (empty( $promptstr ))
					{
						return TRUE;
					}

					throw new TelnetException( "Couldn't find the requested: '{$promptstr}', it was not in the data returned from server: {$this->buffer}" );
				}
				if ($char == $this->IAC)
				{
					$this->handleTelnetOptions();
					continue;
				}
				$this->buffer .= $char;
				if (!empty( $promptstr ) && preg_match("/{$promptstr}$/", $this->buffer))
				{
					return TRUE;
				}

			}
			while ($char != $this->NULL || $char != $this->DC1);
			return FALSE;
		}

		/**
		 * Write command to the socket
		 *
		 * @param string  $buffer      Stuff to write to socket
		 * @param boolean $add_newline Defaults to TRUE, adds newline to the command
		 *
		 * @throws TelnetException
		 * @return boolean
		 */
		protected function write($buffer, $add_newline = TRUE)
		{
			if (!$this->socket)
			{
				throw new TelnetException( "Telnet connection closed" );
			}

			$this->clearBuffer();
			if ($add_newline == TRUE)
			{
				$buffer .= "\n";
			}

			$this->global_buffer .= $buffer;
			if (!fwrite($this->socket, $buffer) < 0)
			{
				throw new TelnetException( "Error writing to socket" );
			}

			return TRUE;
		}

		/**
		 * Returns the content of the buffer
		 *
		 * @return string Content of the buffer
		 */
		protected function getBuffer()
		{
			$buf =
				preg_replace('/\r\n|\r/', "\n", $this->buffer); //Make string conform by replacing all \r or \r\n with \n
			if ($this->strip_promptstr)
			{
				$buf = explode("\n", $buf);
				unset( $buf[count($buf) - 1] ); //removing last line from buffer
				$buf = implode("\n", $buf);
			}
			return trim($buf);
		}

		/**
		 * Reads socket until promptstr is encountered
		 *
		 * @return boolean
		 */
		protected function waitPromptstr()
		{
			return $this->readTo($this->promptstr);
		}

		/**
		 * Telnet control character handling
		 *
		 * @throws TelnetException
		 * @return void
		 */
		protected function handleTelnetOptions()
		{
			$c = $this->getc();
			if ($c != $this->IAC)
			{
				if (( $c == $this->DO ) || ( $c == $this->DONT ))
				{
					$opt = $this->getc();
					fwrite($this->socket, $this->IAC . $this->WONT . $opt);
				}
				else
				{
					if (( $c == $this->WILL ) || ( $c == $this->WONT ))
					{
						$opt = $this->getc();
						fwrite($this->socket, $this->IAC . $this->DONT . $opt);
					}
					else
					{
						throw new TelnetException( 'Error: Unknown control char ' . ord($c) );
					}
				}
			}
			else
			{
				throw new TelnetException( 'Error: Something strange happened' );
			}
		}
	}

	class TelnetException extends Exception
	{
		
	}

?>