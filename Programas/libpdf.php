<?php
	require_once('fpdf.php');
	require_once('libphp.php');
	
	class PDF extends FPDF
	{
		public $ultimoY=0;
		function LastY()
		{
			
			return $this->ultimoY;
		}
		function MeassureRows($text,$maxwidth,$rowheight)
		{
			return $this->PutRows(0,0,$text,$maxwidth,$rowheight,0);
		}
		function PutRows($x,$y,$text,$maxwidth=0,$rowheight=0, $display=1)
		{
			$text = trim(mb_convert_encoding($text, 'iso-8859-1', 'utf-8'));
			if ($maxwidth==0)
				$maxwidth=1000;
			$space = $this->GetStringWidth(' ');
			$line = $text;
			$text = '';
			$incremento = 0;
			$nuevoy=$y;
			
			$words = preg_split('/ +/', $line);
			$width = 0;
			foreach ($words as $word)
			{
				$wordwidth = $this->GetStringWidth($word);
				if ($wordwidth > $maxwidth)
				{
					// Word is too long, we cut it
					$word=$word . ' ';
					for($i=0; $i<strlen($word); $i++)
					{
						$wordwidth = $this->GetStringWidth(substr($word, $i, 1));
						if($width + $wordwidth <= $maxwidth)
						{
							$width +=$wordwidth;
							$text .=substr($word, $i, 1);
						}
						else
						{
							$width = $wordwidth;
							if ($display==1) {
								$this->Text($x,$nuevoy,$text);
							}
							$text = substr($word, $i, 1);
							$nuevoy=$nuevoy+$rowheight;
							$incremento = $incremento + $rowheight;
						}
					}
				}
				elseif($width + $wordwidth <= $maxwidth)
				{
					$width += $wordwidth + $space;
					$text .= $word.' ';
				}
				else
				{
					$width = $wordwidth + $space;
					if ($display==1) {
						$this->Text($x,$nuevoy,$text);
					}
					$text = $word.' ';
					$nuevoy = $nuevoy + $rowheight;
					$incremento = $incremento + $rowheight;
				}
			}
			$incremento = $incremento + $rowheight;
			if ($display==1) {
				$this->Text($x,$nuevoy,$text);
				$this->ultimoY = $y + $incremento;
			}
			return $incremento;
		}	
	}
?>
