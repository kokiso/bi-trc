<?php
session_start();
require('../php/fpdf.php');
$jsonSTR=$_POST['sql'];
$jsonOBJ    = json_decode($jsonSTR);
if (json_last_error() != JSON_ERROR_NONE) { 
  echo "Erro JSON:".json_last_error()." - ".json_last_error_msg()."<br>";
  exit;
}    

$_SESSION['imagem']='../imagens/logoMenor.png';
unset($_SESSION['cabecalho']);
if( isset($jsonOBJ->Cabecalho)):
  $_SESSION['cabecalho'] = $jsonOBJ->Cabecalho;
endif;

class PDF extends FPDF7{
  protected $T128;                                         // Tabela de códigos 128
  protected $ABCset = "";                                  // jogo de personagens elegíveis o C128
  protected $Aset = "";                                    // Definir A do jogo de personagens elegíveis
  protected $Bset = "";                                    // Definir B do jogo de personagens elegíveis
  protected $Cset = "";                                    // Definir C do jogo de personagens elegíveis
  protected $SetFrom;                                      // Conversor de fontes de jogos para o quadro
  protected $SetTo;                                        // Converter jogos de destino para a mesa
  protected $JStart = array("A"=>103, "B"=>104, "C"=>105); // Caracteres de seleção de jogos no início de C128
  protected $JSwap = array("A"=>101, "B"=>100, "C"=>99);   // Personagens de mudança de jogo
  ////////////////////////////////  
  //  Extension du constructeur //
  ////////////////////////////////
  function __construct($orientation='P', $unit='mm', $format='A4') {
    parent::__construct($orientation,$unit,$format);
    $this->T128[] = array(2, 1, 2, 2, 2, 2);           //0 : [ ]               // composição do personagem
    $this->T128[] = array(2, 2, 2, 1, 2, 2);           //1 : [!]
    $this->T128[] = array(2, 2, 2, 2, 2, 1);           //2 : ["]
    $this->T128[] = array(1, 2, 1, 2, 2, 3);           //3 : [#]
    $this->T128[] = array(1, 2, 1, 3, 2, 2);           //4 : [$]
    $this->T128[] = array(1, 3, 1, 2, 2, 2);           //5 : [%]
    $this->T128[] = array(1, 2, 2, 2, 1, 3);           //6 : [&]
    $this->T128[] = array(1, 2, 2, 3, 1, 2);           //7 : [']
    $this->T128[] = array(1, 3, 2, 2, 1, 2);           //8 : [(]
    $this->T128[] = array(2, 2, 1, 2, 1, 3);           //9 : [)]
    $this->T128[] = array(2, 2, 1, 3, 1, 2);           //10 : [*]
    $this->T128[] = array(2, 3, 1, 2, 1, 2);           //11 : [+]
    $this->T128[] = array(1, 1, 2, 2, 3, 2);           //12 : [,]
    $this->T128[] = array(1, 2, 2, 1, 3, 2);           //13 : [-]
    $this->T128[] = array(1, 2, 2, 2, 3, 1);           //14 : [.]
    $this->T128[] = array(1, 1, 3, 2, 2, 2);           //15 : [/]
    $this->T128[] = array(1, 2, 3, 1, 2, 2);           //16 : [0]
    $this->T128[] = array(1, 2, 3, 2, 2, 1);           //17 : [1]
    $this->T128[] = array(2, 2, 3, 2, 1, 1);           //18 : [2]
    $this->T128[] = array(2, 2, 1, 1, 3, 2);           //19 : [3]
    $this->T128[] = array(2, 2, 1, 2, 3, 1);           //20 : [4]
    $this->T128[] = array(2, 1, 3, 2, 1, 2);           //21 : [5]
    $this->T128[] = array(2, 2, 3, 1, 1, 2);           //22 : [6]
    $this->T128[] = array(3, 1, 2, 1, 3, 1);           //23 : [7]
    $this->T128[] = array(3, 1, 1, 2, 2, 2);           //24 : [8]
    $this->T128[] = array(3, 2, 1, 1, 2, 2);           //25 : [9]
    $this->T128[] = array(3, 2, 1, 2, 2, 1);           //26 : [:]
    $this->T128[] = array(3, 1, 2, 2, 1, 2);           //27 : [;]
    $this->T128[] = array(3, 2, 2, 1, 1, 2);           //28 : [<]
    $this->T128[] = array(3, 2, 2, 2, 1, 1);           //29 : [=]
    $this->T128[] = array(2, 1, 2, 1, 2, 3);           //30 : [>]
    $this->T128[] = array(2, 1, 2, 3, 2, 1);           //31 : [?]
    $this->T128[] = array(2, 3, 2, 1, 2, 1);           //32 : [@]
    $this->T128[] = array(1, 1, 1, 3, 2, 3);           //33 : [A]
    $this->T128[] = array(1, 3, 1, 1, 2, 3);           //34 : [B]
    $this->T128[] = array(1, 3, 1, 3, 2, 1);           //35 : [C]
    $this->T128[] = array(1, 1, 2, 3, 1, 3);           //36 : [D]
    $this->T128[] = array(1, 3, 2, 1, 1, 3);           //37 : [E]
    $this->T128[] = array(1, 3, 2, 3, 1, 1);           //38 : [F]
    $this->T128[] = array(2, 1, 1, 3, 1, 3);           //39 : [G]
    $this->T128[] = array(2, 3, 1, 1, 1, 3);           //40 : [H]
    $this->T128[] = array(2, 3, 1, 3, 1, 1);           //41 : [I]
    $this->T128[] = array(1, 1, 2, 1, 3, 3);           //42 : [J]
    $this->T128[] = array(1, 1, 2, 3, 3, 1);           //43 : [K]
    $this->T128[] = array(1, 3, 2, 1, 3, 1);           //44 : [L]
    $this->T128[] = array(1, 1, 3, 1, 2, 3);           //45 : [M]
    $this->T128[] = array(1, 1, 3, 3, 2, 1);           //46 : [N]
    $this->T128[] = array(1, 3, 3, 1, 2, 1);           //47 : [O]
    $this->T128[] = array(3, 1, 3, 1, 2, 1);           //48 : [P]
    $this->T128[] = array(2, 1, 1, 3, 3, 1);           //49 : [Q]
    $this->T128[] = array(2, 3, 1, 1, 3, 1);           //50 : [R]
    $this->T128[] = array(2, 1, 3, 1, 1, 3);           //51 : [S]
    $this->T128[] = array(2, 1, 3, 3, 1, 1);           //52 : [T]
    $this->T128[] = array(2, 1, 3, 1, 3, 1);           //53 : [U]
    $this->T128[] = array(3, 1, 1, 1, 2, 3);           //54 : [V]
    $this->T128[] = array(3, 1, 1, 3, 2, 1);           //55 : [W]
    $this->T128[] = array(3, 3, 1, 1, 2, 1);           //56 : [X]
    $this->T128[] = array(3, 1, 2, 1, 1, 3);           //57 : [Y]
    $this->T128[] = array(3, 1, 2, 3, 1, 1);           //58 : [Z]
    $this->T128[] = array(3, 3, 2, 1, 1, 1);           //59 : [[]
    $this->T128[] = array(3, 1, 4, 1, 1, 1);           //60 : [\]
    $this->T128[] = array(2, 2, 1, 4, 1, 1);           //61 : []]
    $this->T128[] = array(4, 3, 1, 1, 1, 1);           //62 : [^]
    $this->T128[] = array(1, 1, 1, 2, 2, 4);           //63 : [_]
    $this->T128[] = array(1, 1, 1, 4, 2, 2);           //64 : [`]
    $this->T128[] = array(1, 2, 1, 1, 2, 4);           //65 : [a]
    $this->T128[] = array(1, 2, 1, 4, 2, 1);           //66 : [b]
    $this->T128[] = array(1, 4, 1, 1, 2, 2);           //67 : [c]
    $this->T128[] = array(1, 4, 1, 2, 2, 1);           //68 : [d]
    $this->T128[] = array(1, 1, 2, 2, 1, 4);           //69 : [e]
    $this->T128[] = array(1, 1, 2, 4, 1, 2);           //70 : [f]
    $this->T128[] = array(1, 2, 2, 1, 1, 4);           //71 : [g]
    $this->T128[] = array(1, 2, 2, 4, 1, 1);           //72 : [h]
    $this->T128[] = array(1, 4, 2, 1, 1, 2);           //73 : [i]
    $this->T128[] = array(1, 4, 2, 2, 1, 1);           //74 : [j]
    $this->T128[] = array(2, 4, 1, 2, 1, 1);           //75 : [k]
    $this->T128[] = array(2, 2, 1, 1, 1, 4);           //76 : [l]
    $this->T128[] = array(4, 1, 3, 1, 1, 1);           //77 : [m]
    $this->T128[] = array(2, 4, 1, 1, 1, 2);           //78 : [n]
    $this->T128[] = array(1, 3, 4, 1, 1, 1);           //79 : [o]
    $this->T128[] = array(1, 1, 1, 2, 4, 2);           //80 : [p]
    $this->T128[] = array(1, 2, 1, 1, 4, 2);           //81 : [q]
    $this->T128[] = array(1, 2, 1, 2, 4, 1);           //82 : [r]
    $this->T128[] = array(1, 1, 4, 2, 1, 2);           //83 : [s]
    $this->T128[] = array(1, 2, 4, 1, 1, 2);           //84 : [t]
    $this->T128[] = array(1, 2, 4, 2, 1, 1);           //85 : [u]
    $this->T128[] = array(4, 1, 1, 2, 1, 2);           //86 : [v]
    $this->T128[] = array(4, 2, 1, 1, 1, 2);           //87 : [w]
    $this->T128[] = array(4, 2, 1, 2, 1, 1);           //88 : [x]
    $this->T128[] = array(2, 1, 2, 1, 4, 1);           //89 : [y]
    $this->T128[] = array(2, 1, 4, 1, 2, 1);           //90 : [z]
    $this->T128[] = array(4, 1, 2, 1, 2, 1);           //91 : [{]
    $this->T128[] = array(1, 1, 1, 1, 4, 3);           //92 : [|]
    $this->T128[] = array(1, 1, 1, 3, 4, 1);           //93 : [}]
    $this->T128[] = array(1, 3, 1, 1, 4, 1);           //94 : [~]
    $this->T128[] = array(1, 1, 4, 1, 1, 3);           //95 : [DEL]
    $this->T128[] = array(1, 1, 4, 3, 1, 1);           //96 : [FNC3]
    $this->T128[] = array(4, 1, 1, 1, 1, 3);           //97 : [FNC2]
    $this->T128[] = array(4, 1, 1, 3, 1, 1);           //98 : [SHIFT]
    $this->T128[] = array(1, 1, 3, 1, 4, 1);           //99 : [Cswap]
    $this->T128[] = array(1, 1, 4, 1, 3, 1);           //100 : [Bswap]                
    $this->T128[] = array(3, 1, 1, 1, 4, 1);           //101 : [Aswap]
    $this->T128[] = array(4, 1, 1, 1, 3, 1);           //102 : [FNC1]
    $this->T128[] = array(2, 1, 1, 4, 1, 2);           //103 : [Astart]
    $this->T128[] = array(2, 1, 1, 2, 1, 4);           //104 : [Bstart]
    $this->T128[] = array(2, 1, 1, 2, 3, 2);           //105 : [Cstart]
    $this->T128[] = array(2, 3, 3, 1, 1, 1);           //106 : [STOP]
    $this->T128[] = array(2, 1);                       //107 : [END BAR]
  
    for ($i = 32; $i <= 95; $i++) {     // Conjuntos de caracteres
      $this->ABCset .= chr($i);
    }
    $this->Aset = $this->ABCset;
    $this->Bset = $this->ABCset;
    
    for ($i = 0; $i <= 31; $i++) {
      $this->ABCset .= chr($i);
      $this->Aset .= chr($i);
    }
    for ($i = 96; $i <= 127; $i++) {
      $this->ABCset .= chr($i);
      $this->Bset .= chr($i);
    }
    for ($i = 200; $i <= 210; $i++) {   // controle 128
      $this->ABCset .= chr($i);
      $this->Aset .= chr($i);
      $this->Bset .= chr($i);
    }
    $this->Cset="0123456789".chr(206);

    for ($i=0; $i<96; $i++) {           // conversao dos jogos A & B
      @$this->SetFrom["A"] .= chr($i);
      @$this->SetFrom["B"] .= chr($i + 32);
      @$this->SetTo["A"] .= chr(($i < 32) ? $i+64 : $i-32);
      @$this->SetTo["B"] .= chr($i);
    }
    for ($i=96; $i<107; $i++) {         // controle dos jogos A & B
      @$this->SetFrom["A"] .= chr($i + 104);
      @$this->SetFrom["B"] .= chr($i + 104);
      @$this->SetTo["A"] .= chr($i);
      @$this->SetTo["B"] .= chr($i);
    }
  }
  ////////////////////////////////////////////////////
  //  Codificação e função de desenho de código 128 //
  ////////////////////////////////////////////////////
  function Code128($x, $y, $code, $w, $h) {
    $Aguid = "";                                                                      // Criação de guias de seleção ABC
    $Bguid = "";
    $Cguid = "";
    for ($i=0; $i < strlen($code); $i++) {
      $needle = substr($code,$i,1);
      $Aguid .= ((strpos($this->Aset,$needle)===false) ? "N" : "O"); 
      $Bguid .= ((strpos($this->Bset,$needle)===false) ? "N" : "O"); 
      $Cguid .= ((strpos($this->Cset,$needle)===false) ? "N" : "O");
    }

    $SminiC = "OOOO";
    $IminiC = 4;

    $crypt = "";
    ////////////////////////////////////
    //  LOOP PRINCIPAL DE CODIFICAÇÃO //
    ////////////////////////////////////
    while ($code > "") {
      $i = strpos($Cguid,$SminiC);                                                // forçando o jogo C, se possível
      if ($i!==false) {
        $Aguid [$i] = "N";
        $Bguid [$i] = "N";
      }

      if (substr($Cguid,0,$IminiC) == $SminiC) {                                  // Jogo C
        $crypt .= chr(($crypt > "") ? $this->JSwap["C"] : $this->JStart["C"]);    // Inicie o Cstart, caso contrário, Cswap
        $made = strpos($Cguid,"N");                                               // conjunto prolongado C
        if ($made === false) {
          $made = strlen($Cguid);
        }
        if (fmod($made,2)==1) {
          $made--;                                                                // apenas um número par
        }
        for ($i=0; $i < $made; $i += 2) {
          $crypt .= chr(strval(substr($code,$i,2)));                              // Conversão de 2 por 2
        }
        $jeu = "C";
      } else {
        $madeA = strpos($Aguid,"N");                                              // conjunto prolongado A
        if ($madeA === false) {
          $madeA = strlen($Aguid);
        }
        $madeB = strpos($Bguid,"N");                                              // conjunto prolongado B
        if ($madeB === false) {
          $madeB = strlen($Bguid);
        }
        $made = (($madeA < $madeB) ? $madeB : $madeA );                           // tratamento prolongado
        $jeu = (($madeA < $madeB) ? "B" : "A" );                                  // Jogo em andamento
        $crypt .= chr(($crypt > "") ? $this->JSwap[$jeu] : $this->JStart[$jeu]);  // Comece começar, caso contrário, troque
        $crypt .= strtr(substr($code, 0,$made), $this->SetFrom[$jeu], $this->SetTo[$jeu]); // conversão de acordo com o jogo
      }
      $code = substr($code,$made);                                                // encurtar lendas e guias para a área tratada
      $Aguid = substr($Aguid,$made);
      $Bguid = substr($Bguid,$made);
      $Cguid = substr($Cguid,$made);
    } 
    ////////////////////////////
    // FIM DO LOOP PRINCIPAL  //
    ////////////////////////////
    $check = ord($crypt[0]);                                                      // cálculo da soma de verificação
    for ($i=0; $i<strlen($crypt); $i++) {
      $check += (ord($crypt[$i]) * $i);
    }
    $check %= 103;

    $crypt .= chr($check) . chr(106) . chr(107);                                  // Cadeia completa criptografada

    $i = (strlen($crypt) * 11) - 8;                                               // calculando a largura do módulo
    $modul = $w/$i;
    ////////////////////////
    // LOOP DE IMPRESSAO  //
    ////////////////////////
    for ($i=0; $i<strlen($crypt); $i++) {
      $c = $this->T128[ord($crypt[$i])];
      for ($j=0; $j<count($c); $j++) {
        $this->Rect($x,$y,$c[$j]*$modul,$h,"F");
        $x += ($c[$j++]+$c[$j])*$modul;
      }
    }
  }
  //
  // Page header
  function Header(){
    //akiii
    //                               x ,y, w, h
    $this->Image($_SESSION['imagem'],10,6,40,17);
    
    // Arial bold 15
    $this->SetFont('Arial','',15);
    // Move to the right
    $this->Cell(80);
    // Title
    $this->Cell(0,5,'Trac',0,0,'R',false);
    // Line break
    $this->Ln(10);
    if (isset($_SESSION['cabecalho'])){
      $cab   = $_SESSION['cabecalho']; 
      foreach ( $cab as $grupo ):
        if( isset($grupo->SetFont)):
          $this->SetFont($grupo->SetFont[0], $grupo->SetFont[1],$grupo->SetFont[2]);			
        endif;
        if( isset($grupo->Cell)):
          $this->Cell($grupo->Cell[0], $grupo->Cell[1],utf8_decode($grupo->Cell[2]),$grupo->Cell[3], $grupo->Cell[4],$grupo->Cell[5]);   
        endif;      
        if( isset($grupo->Imagem)):
          $this->Image($grupo->Imagem[0], $grupo->Imagem[1],$grupo->Imagem[2],$grupo->Imagem[3], $grupo->Imagem[4]);   
        endif;      
        if( isset($grupo->Ln)):
          $this->Ln($grupo->Ln[0]);			
        endif;		
      endforeach;
    }
  }
  ////////////
  // Footer //   
  ////////////
  function Footer(){
    $this->SetY(-15);                                             // Position at 1.5 cm from bottom
    $this->SetFont('Arial','I',8);                                // Arial italic 8
    $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');  // Page number
  }
}


if (isset($jsonOBJ->orientacao)) {
  if ($jsonOBJ->orientacao=='P') 
    $pdf = new PDF('L','mm','A4'); //landscape
  else
    $pdf = new PDF('P','mm','A4'); //portrait
}
else
  $pdf = new PDF('P','mm','A4'); 
//--
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont("Arial",'', 12); //Define a fonte a ser utilizada
$pdf->SetAuthor("Autor: BisSystem"); 
//--
    //--
    $imprimir   = $jsonOBJ->imprimir; 
    foreach ( $imprimir as $grupo ):
		  if( isset($grupo->SetFont)):
				$pdf->SetFont($grupo->SetFont[0], $grupo->SetFont[1],$grupo->SetFont[2]);			
			endif;
      if( isset($grupo->Cell)):
        $pdf->Cell($grupo->Cell[0], $grupo->Cell[1],utf8_decode($grupo->Cell[2]),$grupo->Cell[3], $grupo->Cell[4],$grupo->Cell[5]);   
      endif;      
      if( isset($grupo->Imagem)):
        $pdf->Image($grupo->Imagem[0], $grupo->Imagem[1],$grupo->Imagem[2],$grupo->Imagem[3], $grupo->Imagem[4]);   
      endif;      
		  if( isset($grupo->Ln)):
				$pdf->Ln($grupo->Ln[0]);			
			endif;
      if( isset($grupo->SetFillColor) ):
         if( $grupo->SetFillColor[0]=='cinza' ):
          $pdf->SetFillColor(216,216,191);
        endif;
        if( $grupo->SetFillColor[0]=='branco' ):
          $pdf->SetFillColor(255,255,255);
        endif;
        if( $grupo->SetFillColor[0]=='amareloclaro' ):
          $pdf->SetFillColor(255,250,205);
        endif;
        if( $grupo->SetFillColor[0]=='cinzaclaro' ):
          $pdf->SetFillColor(238,233,233);
        endif;
        if( $grupo->SetFillColor[0]=='vermelhoclaro' ):
          $pdf->SetFillColor(238,180,180);
        endif;
        if( $grupo->SetFillColor[0]=='azulclaro' ):
          $pdf->SetFillColor(100,199,237);
        endif;
         $pdf->Cell($grupo->SetFillColor[2],$grupo->SetFillColor[1], ' ',0,0,'L',true);
        $pdf->SetX(10);
			endif;		
 		  if( isset($grupo->SetTextColor) ):
        if( $grupo->SetTextColor[0]=='azul' )
          $pdf->SetTextColor(0,0,100);
        if( $grupo->SetTextColor[0]=='vermelho' )
          $pdf->SetTextColor(210,0,0);
        if( $grupo->SetTextColor[0]=='preto' )
          $pdf->SetTextColor(0,0,0);
			endif;		
      if (isset($grupo->AddPage)):
        $pdf->AddPage('','');
      endif;      
      if( isset($grupo->CodeBar128)):
        //0=x, 1=y, 2=codigo, 3=w, 4=h
        $pdf->Code128($grupo->CodeBar128[0],$grupo->CodeBar128[1],$grupo->CodeBar128[2],$grupo->CodeBar128[3],$grupo->CodeBar128[4]);
			endif;
  		endforeach;
$pdf->Output();
?>