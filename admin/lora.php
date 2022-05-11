<?php

require( '../config.php' );
require( '../inc/php/global.php' ); 

// check login
if( !$usr = iw_login_check( $con, 'iw_admins', 'login1', 'a' ) ) { header( 'location: login.php?notlogged' ); exit; }



?>
<? require ( '../inc/tpl/admin_meta.php' ); ?>
</head>
<body>
<? require ( '../inc/tpl/admin_header.php' ); ?>




<iframe id="iframe" src="https://obmd-domo.duckdns.org:1880/ui/#!/0?socketid=H7P5lvmU4gD30fJPAABp" frameborder="0" style="width: calc( 100% + 4.2rem ); margin: -2.1rem;"></iframe>

	
	

<? require ( '../inc/tpl/admin_footer.php' ); ?>
	
<script>
// resize iframe
var menuHeight = 80 + 20;
var totalHeight = isNaN(window.innerHeight) ? window.clientHeight : window.innerHeight;
document.getElementById('iframe').height = totalHeight - menuHeight;
</script>	
</body>
</html>