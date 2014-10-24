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
		<link rel="import" href="bower_components/core-tooltip/core-tooltip.html">
		<link rel="import" href="bower_components/paper-tabs/paper-tabs.html">
		
		<link rel="import" href="elements/song-box.html">
		<link rel="import" href="elements/post-card.html">

		<link rel="stylesheet" href="css/style.css">
        
        <style shim-shadowdom="">
            core-tooltip.fancy::shadow .core-tooltip {
              opacity: 0;
              -webkit-transition: all 300ms cubic-bezier(0,1.92,.99,1.07);
              transition: all 300ms cubic-bezier(0,1.92,.99,1.07);
              -webkit-transform: translate3d(0, -10px, 0);
              transform: translate3d(0, -10px, 0);
            }

            core-tooltip.fancy:hover::shadow .core-tooltip,
            core-tooltip.fancy:focus::shadow .core-tooltip {
              opacity: 1;
              -webkit-transform: translate3d(0, 0, 0);
              transform: translate3d(0, 0, 0);
            }
        </style>
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
                    <core-tooltip label="Is a truck!" class="fancy" role="tooltip">
					    <h2>Your mom</h2>
                    </core-tooltip>
					<h3>4:20</h3>
				</div>
			</song-box>
			<br>
			<?php
                $MEDIA_DIR = './media';
				iterate_dir($MEDIA_DIR . '');
				
				function iterate_dir($dir) {
                    global $MEDIA_DIR;
					$files = scandir($dir);
					sort($files);
					foreach($files as $file) {
						if(strlen($file) > 2 && (strpos($file, '.mp3') != 0 || strpos($file, '.') == 0)) {
								$href = "$dir/$file";
								if(strpos($file, '.') == 0) {
									iterate_dir($href);
                                } else {
                                    build_box($file, str_replace( $MEDIA_DIR . '/' , '' , $href ) );
                                }
						}	
					}
				}
				
				function build_box($name, $href) {
					$tokens = explode(' - ', str_replace('.mp3', '', $name));
					$artist = $tokens[0]; // TODO change to proper id3
					$name = $tokens[1];
					$length = '1:23'; // TODO Fix >_>
					$img_url = '//' . $_SERVER["HTTP_HOST"] . '/api/v1/art.php?file=' . $href;
					
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
                document.querySelector('song-box').addEventListener('play', function(e) {
                    console.log(e.type, e.detail.title); //TODO: Toast
                });
            
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
