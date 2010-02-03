// JavaScript Document

function getObj(name) {
	if (document.getElementById) {
		this.obj = document.getElementById(name);
		this.style = document.getElementById(name).style;
	} else if (document.all) {
		this.obj = document.all[name];
		this.style = document.all[name].style;
	}
	else if (document.layers) {
		this.obj = document.layers[name];
		this.style = document.layers[name];
	}
}


var recent_count = 0;
var recent_moving = 0;
var temp_x;
var temp_count = 0;
var temp_link;
var temp_linkage;
var temp_ind;
var loaded_count = 0;
function scroll_down() {
	var x = new getObj('recent_posts_list');
	var currentpx = -recent_count * 182;
	var nextpx = -(recent_count+1) * 182;

	if(recent_moving == 0) {
		temp_x = currentpx;
		recent_moving = 1;
		temp_count = 0;
	} else {
		temp_x -= (30-temp_count*2);
	}
	
	if ((temp_x <= currentpx) && (temp_x >= nextpx)) {
		x.style.top = temp_x + "px";
		temp_count++;
		setTimeout('scroll_down()', 30);
	} else if (temp_x < nextpx) {
		x.style.top = nextpx+"px";
		recent_moving = 0;
		recent_count++;
		temp_linkage.onclick = temp_link;
		temp_ind.style.display = 'none';
		return;
	}

}
function scroll_down_call(linkage) {
	var ind = new getObj('ajaxload_home');
	temp_link = linkage.onclick;
	temp_linkage = linkage;
	linkage.onclick = null;
	temp_ind = ind;
	ind.style.display = 'block';
	
	if(recent_count == loaded_count) {
		x_get_laid(recent_count, scroll_down_insert);
	} else {
		scroll_down();
	}
}
function scroll_down_insert(z) {
	loaded_count++;
	var ul = new getObj('recent_posts_list');
	document.getElementById("recent_posts_list").innerHTML += z;
	scroll_down();
}
function scroll_up(linkage)
{
	if(recent_count < 1) {
		return;
	}
	var x = new getObj('recent_posts_list');
	var ind = new getObj('ajaxload_home');
	var currentpx = -recent_count * 182;
	var nextpx = -(recent_count-1) * 182;

	if(recent_moving == 0) {
		temp_x = currentpx;
		recent_moving = 1;
		temp_count = 0;
		temp_link = linkage.onclick;
		temp_linkage = linkage;
		linkage.onclick = null;
		temp_ind = ind;
		ind.style.display = 'block';
	} else {
		temp_x += (30-temp_count*2);
	}
	
	if ((temp_x >= currentpx) && (temp_x <= nextpx)) {
		x.style.top = temp_x + "px";
		temp_count++;
		setTimeout('scroll_up()', 30);
	} else if (temp_x > nextpx) {
		x.style.top = nextpx+"px";
		recent_moving = 0;
		recent_count--;
		temp_linkage.onclick = temp_link;
		temp_ind.style.display = 'none';
		return;
	}
}


/*** Email ***/
function send_email_cb(z) {
	document.getElementById("email_sent").style.display = "block";
	document.getElementById("sending_email").style.display = "none";
}
function sendtheemail_styles() {
	document.getElementById("email_send").disabled = true;
	document.getElementById("sending_email").style.display = "block";
}

function sendtheemail() {
	// get the folder name
	var email_name, email_email, email_website, email_message, alert_error;
	
	email_name = document.getElementById("email_name").value;
	email_email = document.getElementById("email_email").value;
	email_website = document.getElementById("email_website").value;
	email_message = document.getElementById("email_message").value;
	
	alert_error = "";
	
	if(email_name.length < 1) {
		alert_error += "Please put in your name.\n";
	}
	if(email_email.length < 1) {
		alert_error += "Please put in your email.\n";
	} else if (!checkemail(email_email)) {
		alert_error += "Please enter a valid email address.\n";
	}
	if(email_message.length < 1) {
		alert_error += "Please enter a message.";
	}
	
	if(alert_error.length > 0) {
		alert(alert_error);
	} else {	
		x_send_email(email_name, email_email, email_website, email_message, send_email_cb);
		sendtheemail_styles();
	}
}
function checkemail(str) {
	var at="@"
	var dot="."
	var lat=str.indexOf(at)
	var lstr=str.length
	var ldot=str.indexOf(dot)
	if (str.indexOf(at)==-1){
		return false;
	}
	if (str.indexOf(at)==-1 || str.indexOf(at)==0 || str.indexOf(at)==lstr){
		return false;
	}
	if (str.indexOf(dot)==-1 || str.indexOf(dot)==0 || str.indexOf(dot)==lstr){
		return false;
	}
	if (str.indexOf(at,(lat+1))!=-1){
		return false;
	}
	if (str.substring(lat-1,lat)==dot || str.substring(lat+1,lat+2)==dot){
		return false;
	}
	if (str.indexOf(dot,(lat+2))==-1){
		return false;
	}
	if (str.indexOf(" ")!=-1){
		return false;
	}
	return true;
}