var isMobile=false;
if(window.matchMedia("(max-width: 768px)").matches){
	isMobile='mobile';
}else if(window.matchMedia("(max-width: 1023px)").matches){
	isMobile='tablet';
}