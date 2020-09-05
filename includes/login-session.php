<?php
function isLoginSessionExpired() {
	$login_session_duration = 60 * 60 * 2;   // 2 hours
	$current_time = time(); 
	if(isset($_SESSION['login_timeout']) && isset($_SESSION["user_id"])){  
		if(((time() - $_SESSION['login_timeout']) > $login_session_duration)){ 
			return true; 
		} 
	}
	return false;
}