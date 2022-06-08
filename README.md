# BadChess
A horrible PHP Chess game engine with playable web page (index.php), command line interface (versus.php) and command line machine learning (MLLearner.php)

Everything is programmed in PHP without the use of any library. (Yikes)

<hr>
<h2>Installation</h2>
Clone the repository, install PHP 8.1, run `composer dumpautoload`, start a php server with `php -S 127.0.0.1:3333 -t .` inside the directory (might need sudo for that) and open the page http://127.0.0.1:3333 in your browser or instead run `php versus.php` in the command line. (No running server required for versus.php)

<hr>
<h2>Screenshots</h2>

<img width="699" alt="taa_screenshot 2022-06-08 at 10 44 02" src="https://user-images.githubusercontent.com/66881998/172573236-961a7c8e-9525-4240-8d51-aec25e9568b8.png">
The index.php page can be interacted with by clicking the piece you want to move and the field you want to put the piece on. You always start as white (I know, it's bad). 
You can also enter moves according to this style of notation: "E2E4" or "XXYY", "XX" being the field of the piece you want to move and "YY" the position the piece should go.
<hr>
<img width="699" src="https://user-images.githubusercontent.com/66881998/172573308-a09e45be-e1aa-4817-8806-b6a0ca15433f.png">
The notation for the move is the same as for the page. "E2E4", "E1D1", "B1C3", etc.<hr>
https://user-images.githubusercontent.com/66881998/172574121-b0a1c161-6d28-4b8e-8ed4-78dca64e3e74.mov<br>
Learning the moves and putting them into "learnt.json" so the play experience is more or less instant without thinking time for the machine.
