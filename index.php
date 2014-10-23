<!doctype html>
<html>
	<head>
		<title>unquote</title>

		<meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1.0, user-scalable=yes">
		
		<script src="/bower_components/webcomponents.js/webcomponents.js"></script>
        
        <link rel="import" href="bower_components/core-ajax/core-ajax.html">
		<link rel="import" href="bower_components/font-roboto/roboto.html">
		<link rel="import" href="bower_components/core-header-panel/core-header-panel.html">
		<link rel="import" href="bower_components/core-toolbar/core-toolbar.html">
		<link rel="import" href="bower_components/paper-tabs/paper-tabs.html">
		
		<link rel="import" href="elements/song-box.html">
		<link rel="import" href="elements/post-card.html">

		<link rel="stylesheet" href="css/style.css">
	</head>

	<body unresolved>
        <core-ajax url="//localhost:8192/api/v1/list.php"
               handleAs="json"></core-ajax>
    
		<core-header-panel>
			<core-toolbar>
				<paper-tabs id="toolbar-tabs" selected="home" self-end>
					<paper-tab name="home">Home</paper-tab>
					<paper-tab name="jpop">JPop</paper-tab>
					<paper-tab name="vocaloid">Vocaloid</paper-tab>
					<paper-tab name="nightcore">Nightcore</paper-tab>
				</paper-tabs>
			</core-toolbar>


		<div class="container">
			<song-box>
				<img src="http://2.bp.blogspot.com/-8v3Ft3nJZbU/U_qk2COThcI/AAAAAAAAAA8/XvLarrCcqWo/s640/No%2Btitle%2B.jpg"></img>
				<div>
					<h1>This is a test song</h1>
					<h2>Your mom</h2>
					<h3>4:20</h3>
				</div>
			</song-box>
			<br>
			<?php
				iterate_dir('./media');
				
				function iterate_dir($dir) {
                    $root = $dir;
					$files = scandir($dir);
					sort($files);
					foreach($files as $file) {
						if(strlen($file) > 2 && (strpos($file, '.mp3') != 0 || strpos($file, '.') == 0)) {
								$href = "$dir/$file";
								if(strpos($file, '.') == 0) {
									iterate_dir($href);
                                } else {
                                    build_box($file, str_replace('./media', '', $href);
                                }
						}	
					}
				}
				
				function build_box($name, $href) {
					$tokens = explode(' - ', str_replace('.mp3', '', $name));
					$artist = $tokens[0]; // TODO change to proper id3
					$name = $tokens[1];
					$length = '1:23'; // TODO Fix >_>
					$img_url = 'http://10.0.0.32:8192/api/v1/art.php?file=' . $href;
					
					echo "<song-box><img src='$img_url'></img><div><h1>$name</h1><h2>$artist</h2><h3>$length</h3></div></song-box><br>";
				}
			?>
		</div>
		</core-header-panel>
		
		<script>
			var tabs = document.querySelector('paper-tabs');
			tabs.addEventListener('core-select', function(e) {
				console.log("Selected: " + tabs.selected);
			});
            
            // Wait for 'polymer-ready'. Ensures the element is upgraded.
            window.addEventListener('polymer-ready', function(e) {
              var ajax = document.querySelector('core-ajax');

              // Respond to events it fires.
              ajax.addEventListener('core-response', function(e) {
                console.log(this.response);
              });

              ajax.go(); // Call its API methods.
            });
		</script>
	</body>
</html>
