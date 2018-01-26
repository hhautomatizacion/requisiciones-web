<?php
	require_once('fpdf.php');
	
	class PDF extends FPDF
	{
		function MeassureRows($text,$maxwidth,$rowheight)
		{
			return $this->PutRows(0,0,$text,$maxwidth,$rowheight,0);
		}
		function PutRows($x,$y,$text,$maxwidth=0,$rowheight=0, $display=1)
		{
			$text = trim($text);
			//if ($text==='')
			//	return 0;
			if ($maxwidth==0)
				$maxwidth=1000;
			$space = $this->GetStringWidth(' ');
			$line = $text;
			$text = '';
			$incremento = 0;
			
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
								$this->Text($x,$y,$text);
							}
							$text = substr($word, $i, 1);
							$y=$y+$rowheight;
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
						$this->Text($x,$y,$text);
					}
					$text = $word.' ';
					$y = $y + $rowheight;
					$incremento = $incremento + $rowheight;
				}
			}
			if ($display==1) {
				$this->Text($x,$y,$text);
			}
			$incremento = $incremento + $rowheight;
			
			return $incremento;
		}
		
		
	}
?>
