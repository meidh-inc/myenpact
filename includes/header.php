<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>MyENpact, my environmental impact! - by ~MeidhTech~</title>
		<link rel="stylesheet" type="text/css" href="stylesheets/style.css" />



<!-- BEGIN: javascript for company name hinting -->
<script type="text/javascript">
function showHint(str)
{
if (str.length==0)
  { 
  document.getElementById("txtHint").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("txtHint").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","includes/company_hint.php?q="+str,true);
xmlhttp.send();
}
</script>
<!-- END: javascript for company name hinting -->


<!-- BEGIN: javascript for company zipcode hinting -->
<script type="text/javascript">
function showHint2(str)
{
if (str.length==0)
  { 
  document.getElementById("ozipHint").innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("ozipHint").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET","includes/zipcode_hint.php?z="+str,true);
xmlhttp.send();
}
</script>
<!-- END: javascript for company zipcode hinting -->


    </head>
    <body>

        <div id="header" style="width:100%;">
			<div id="header_bar"></div><center>
<style>
    <div style="float: left; width: 200px;">Left Stuff</div>  <div style="float: left; width: 100px;">Middle Stuff</div>  <div style="float: left; width: 200px;">Right Stuff</div>  <br style="clear: left;" /> 
</style>
<style> .clear-fix{clear:both;}</style>
	
	</div>
<div style="width:100%"><center>
	<div id="outer_div" style="background:url(images/header_image_b_flat.png) no-repeat;height:130px;">
	<div id="outer_div">
		<div id="header-content">
			<!--<table width="100%" border="0" style="border-bottom:solid thin black;">-->
			<table width="100%" border="0">
				<tr>
					<td rowspan='2' width='105px' height='110px'><img src="images/cosc_evergreen_main_small_2.png"></td>
					<td colspan='2' align="left">
						<div style="float:left;width:700px;height:35px;font-weight:normal;font-size:25px;color:white;padding-top:10px;">
						&nbsp;&nbsp;&nbsp;Your trusted measure of sustainability.
						</div></td>
				</tr>
				<tr>
					<td align="left">
						<a href="http://icosc.com" target="_blank" style="text-decoration:none; color:black;">
							&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<font color=#F4911E>Visit COSC</font></a></td>
					<td align="right">
						<a href='faq.php' target="_blank" style="text-decoration:none; color:black;"><font color=#F4911E>FAQ</font></a>
						&nbsp;&nbsp;<font color=#999999>|</font>&nbsp;&nbsp;
						<a href='contact.php' target="_blank" style="text-decoration:none; color:black;"><font color=#F4911E>Contact</font></a>
						&nbsp;&nbsp;<font color=#999999>|</font>&nbsp;&nbsp;
						<a href='feedback.php' style="text-decoration:none; color:black;"><font color=#F4911E>Feedback</font></a>
						<!-- &nbsp;&nbsp;<font color=#999999>|</font>&nbsp;&nbsp;
						 <font color=#F4911E>Shop</font> -->
					</td>
				</tr>
			</table>
	<div class="clear-fix"></div>
	
		</div>
	</div>
</div>


