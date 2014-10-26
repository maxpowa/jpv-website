// Source: http://wowmotty.blogspot.pt/2009/06/convert-jquery-rgb-output-to-hex-color.html
// (Modified for use)

const hexDigits = new Array("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"); 

function rgb2hex(rgb) {
	rgb = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
	return "#" + hex(rgb[1]) + hex(rgb[2]) + hex(rgb[3]);
}

function hex2dec(hex) {
	if(hex.startsWith("#"))
		hex = hex.substring(1);
	return parseInt(hex, 16);
}

function rgb2dec(rgb) {
	return hex2dec(rgb2hex(rgb));
}

function hex(x) {
	return isNaN(x) ? "00" : hexDigits[(x - x % 16) / 16] + hexDigits[x % 16];
}