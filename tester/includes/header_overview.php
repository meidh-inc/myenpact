<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title>rian project and task management by meidh tech</title>
		<link rel="stylesheet" type="text/css" href="stylesheets/style.css" />
		
		<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
		<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
		<script>
		    $(document).ready(function() {
		    $("#datepicker").datepicker();
		    $("#datepicker2").datepicker();
		    $("#datepicker3").datepicker();
		    $(".expand").click(function () { $("#mytable tbody").show("slow");});
		    $(".collapse").click(function () { $("#mytable tbody").hide("fast");});
		    $(".expand2").click(function () { $("#mytable2 tbody").show("slow");});
		    $(".collapse2").click(function () { $("#mytable2 tbody").hide("fast");});
		    });
		</script>

    </head>
    <body>
        <div id="header">
	    <div id="header_bar"></div>
	</div>
<style> #clear-fix{clear:both;}</style>