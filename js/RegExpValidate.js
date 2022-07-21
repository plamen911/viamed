
/*******************************************************************************
FILE: RegExpValidate.js

DESCRIPTION: This file contains a library of validation functions
using javascript regular expressions.  Library also contains functions that re-
format fields for display or for storage.


VALIDATION FUNCTIONS:

validateSS - checks format of Social Security Number
validateEmail - checks format of email address
validateUSPhone - checks format of US phone number
validateUSState - checks for valid US state
validateNumeric - checks for valid numeric value
validateInteger - checks for valid integer value
validateNotEmpty - checks for blank form field
validateUSZip - checks for valid US zip code
validateUSDate - checks for valid date in US format
validateValue - checks a string against supplied pattern
validateDate - checks for valid date in US format
compareDates - compares two dates

FORMAT FUNCTIONS:

rightTrim - removes trailing spaces from a string
leftTrim - removes leading spaces from a string
trimAll - removes leading and trailing spaces from a string
removeCurrency - removes currency formatting characters (), $
addCurrency - inserts currency formatting characters
removeCommas - removes comma separators from a number
addCommas - adds comma separators to a number
removeCharacters - removes characters from a string that match passed pattern

autoformatSSN - Autoformat Social Security Number in format: 999-99-9999
formatPhone - Autoformat US Phone Number in format: (XXX)XXX-XXXX
numbersonly - Restricting a Field to Numbers Only

insertAtCursor

AUTHOR: Karen Gayda

DATE: 03/24/2000
*******************************************************************************/

function validateSS( strValue ) {
	/************************************************
	DESCRIPTION: Validates that a string a matches
	a valid Social Security Number pattern.

	PARAMETERS:
	strValue - String to be tested for validity

	RETURNS:
	True if valid, otherwise false.
	Social Security Number
	/^\d{3}\-?\d{2}\-?\d{4}$/   999-99-9999 or 999999999
	*************************************************/
	var objRegExp = /^\d{3}\-\d{2}\-\d{4}$/;

	//check for valid Social Security Number
	return objRegExp.test(strValue);
}

function validateEmail( strValue) {
	/************************************************
	DESCRIPTION: Validates that a string contains a
	valid email pattern.

	PARAMETERS:
	strValue - String to be tested for validity

	RETURNS:
	True if valid, otherwise false.

	REMARKS: Accounts for email with country appended
	does not validate that email contains valid URL
	type (.com, .gov, etc.) or valid country suffix.
	*************************************************/
	// are regular expressions supported?
	var supported = 0;
	if (window.RegExp) {
		var tempStr = "a";
		var tempReg = new RegExp(tempStr);
		if (tempReg.test(tempStr)) supported = 1;
	}
	if (!supported)
	return (str.indexOf(".") > 2) && (str.indexOf("@") > 0);

	var objRegExp  = /(^[a-z]([a-z0-9_\.-]*)@([a-z_\.]*)([.][a-z]{3})$)|(^[a-z]([a-z_\.]*)@([a-z_\.]*)(\.[a-z]{3})(\.[a-z]{2})*$)/i;

	//check for valid email
	return objRegExp.test(strValue);
}

function validateUSPhone( strValue ) {
	/************************************************
	DESCRIPTION: Validates that a string contains valid
	US phone pattern.
	Ex. (999) 999-9999 or (999)999-9999

	PARAMETERS:
	strValue - String to be tested for validity

	RETURNS:
	True if valid, otherwise false.
	*************************************************/
	var objRegExp  = /^\([1-9]\d{2}\)\s?\d{3}\-\d{4}$/;

	//check for valid us phone with or without space between
	//area code
	return objRegExp.test(strValue);
}

function validateUSPhone2( strValue ) {
	/************************************************
	DESCRIPTION: Validates that a string contains valid
	US phone pattern.
	Ex. (999) 999-9999 or (999)999-9999 or 999-999-9999 or 1-999-999-9999

	PARAMETERS:
	strValue - String to be tested for validity

	RETURNS:
	True if valid, otherwise false.
	*************************************************/
	var objRegExp  = /(^\([1-9]\d{2}\)\s?\d{3}\-\d{4}$)|(^\d{3}\-\d{3}\-\d{4}$)|(^\d{1}\-d{3}\-\d{3}\-\d{4}$)/;
	//check for valid us phone with or without space between
	//area code
	return objRegExp.test(strValue);
}

function validateUSState(strValue) {
	/************************************************
	DESCRIPTION: Validates that a string contains valid
	US state 2-letter abbreviation.
	Ex. MA or LA

	PARAMETERS:
	strValue - String to be tested for validity

	RETURNS:
	True if valid, otherwise false.
	*************************************************/
	// All states array
	states = new Array('AK','AL','AR','AS','AZ','CA','CO','CT','DC','DE','FL','FM','GA','GU','HI','IA','ID','IL','IN','KS','KY','LA','MA','MD','ME','MH','MI','MN','MO','MS','MT','NC','ND','NE','NH','NJ','NM','NV','NY','OH','OK','OR','PA','PR','RI','SC','SD','TN','TX','UT','VA','VI','VT','WA','WI','WV','WY');
	for(i=0; i<states.length; i++) {
		if(strValue==states[i]) return true;
	}
	return false;
}

function  validateNumeric( strValue ) {
	/******************************************************************************
	DESCRIPTION: Validates that a string contains only valid numbers.

	PARAMETERS:
	strValue - String to be tested for validity

	RETURNS:
	True if valid, otherwise false.
	******************************************************************************/
	var objRegExp  =  /(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/;

	//check for numeric characters
	return objRegExp.test(strValue);
}

function validateInteger( strValue ) {
	/************************************************
	DESCRIPTION: Validates that a string contains only
	valid integer number.

	PARAMETERS:
	strValue - String to be tested for validity

	RETURNS:
	True if valid, otherwise false.
	******************************************************************************/
	var objRegExp  = /(^-?\d\d*$)/;

	//check for integer characters
	return objRegExp.test(strValue);
}

function validateNotEmpty( strValue ) {
	/************************************************
	DESCRIPTION: Validates that a string is not all
	blank (whitespace) characters.

	PARAMETERS:
	strValue - String to be tested for validity

	RETURNS:
	True if valid, otherwise false.
	*************************************************/
	var strTemp = strValue;
	strTemp = trimAll(strTemp);
	if(strTemp.length > 0){
		return true;
	}
	return false;
}

function validateUSZip( strValue ) {
	/************************************************
	DESCRIPTION: Validates that a string a United
	States zip code in 5 digit format or zip+4
	format. 99999 or 99999-9999

	PARAMETERS:
	strValue - String to be tested for validity

	RETURNS:
	True if valid, otherwise false.

	*************************************************/
	var objRegExp  = /(^\d{5}$)|(^\d{5}-\d{4}$)/;

	//check for valid US Zipcode
	return objRegExp.test(strValue);
}

function validateUSDate( strValue ) {
	/************************************************
	DESCRIPTION: Validates that a string contains only
	valid dates with 2 digit month, 2 digit day,
	2 or 4 digit year. Date separator can be ., -, or /.
	Uses combination of regular expressions and
	string parsing to validate date.
	Ex. mm/dd/yyyy or mm-dd-yyyy or mm.dd.yyyy

	PARAMETERS:
	strValue - String to be tested for validity

	RETURNS:
	True if valid, otherwise false.

	REMARKS:
	Avoids some of the limitations of the Date.parse()
	method such as the date separator character.
	*************************************************/
	var objRegExp = /^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{2,4}$/

	//check to see if in correct format
	if(!objRegExp.test(strValue))
	return false; //doesn't match pattern, bad date
	else{
		var strSeparator = strValue.substring(2,3) //find date separator
		var arrayDate = strValue.split(strSeparator); //split date into month, day, year
		//create a lookup for months not equal to Feb.
		var arrayLookup = { '01' : 31,'03' : 31, '04' : 30,'05' : 31,'06' : 30,'07' : 31,
		'08' : 31,'09' : 30,'10' : 31,'11' : 30,'12' : 31}
		var intDay = parseInt(arrayDate[1],10);

		//check if month value and day value agree
		if(arrayLookup[arrayDate[0]] != null) {
			if(intDay <= arrayLookup[arrayDate[0]] && intDay != 0)
			return true; //found in lookup table, good date
		}

		//check for February (bugfix 20050322)
		//bugfix  for parseInt kevin
		//bugfix  biss year  O.Jp Voutat
		var intMonth = parseInt(arrayDate[0],10);
		if (intMonth == 2) {
			var intYear = parseInt(arrayDate[2]);
			if (intDay > 0 && intDay < 29) {
				return true;
			}
			else if (intDay == 29) {
				if ((intYear % 4 == 0) && (intYear % 100 != 0) ||
				(intYear % 400 == 0)) {
					// year div by 4 and ((not div by 100) or div by 400) ->ok
					return true;
				}
			}
		}
	}
	return false; //any other values, bad date
}

function validateValue( strValue, strMatchPattern ) {
	/************************************************
	DESCRIPTION: Validates that a string a matches
	a valid regular expression value.

	PARAMETERS:
	strValue - String to be tested for validity
	strMatchPattern - String containing a valid
	regular expression match pattern.

	RETURNS:
	True if valid, otherwise false.
	*************************************************/
	var objRegExp = new RegExp( strMatchPattern);

	//check if string matches pattern
	return objRegExp.test(strValue);
}


function rightTrim( strValue ) {
	/************************************************
	DESCRIPTION: Trims trailing whitespace chars.

	PARAMETERS:
	strValue - String to be trimmed.

	RETURNS:
	Source string with right whitespaces removed.
	*************************************************/
	var objRegExp = /^([\w\W]*)(\b\s*)$/;

	if(objRegExp.test(strValue)) {
		//remove trailing a whitespace characters
		strValue = strValue.replace(objRegExp, '$1');
	}
	return strValue;
}

function leftTrim( strValue ) {
	/************************************************
	DESCRIPTION: Trims leading whitespace chars.

	PARAMETERS:
	strValue - String to be trimmed

	RETURNS:
	Source string with left whitespaces removed.
	*************************************************/
	var objRegExp = /^(\s*)(\b[\w\W]*)$/;

	if(objRegExp.test(strValue)) {
		//remove leading a whitespace characters
		strValue = strValue.replace(objRegExp, '$2');
	}
	return strValue;
}

function trimAll( strValue ) {
	/************************************************
	DESCRIPTION: Removes leading and trailing spaces.

	PARAMETERS: Source string from which spaces will
	be removed;

	RETURNS: Source string with whitespaces removed.
	*************************************************/
	var objRegExp = /^(\s*)$/;

	//check for all spaces
	if(objRegExp.test(strValue)) {
		strValue = strValue.replace(objRegExp, '');
		if( strValue.length == 0)
		return strValue;
	}

	//check for leading & trailing spaces
	objRegExp = /^(\s*)([\W\w]*)(\b\s*$)/;
	if(objRegExp.test(strValue)) {
		//remove leading and trailing whitespace characters
		strValue = strValue.replace(objRegExp, '$2');
	}
	return strValue;
}

function removeCurrency( strValue ) {
	/************************************************
	DESCRIPTION: Removes currency formatting from
	source string.

	PARAMETERS:
	strValue - Source string from which currency formatting
	will be removed;

	RETURNS: Source string with commas removed.
	*************************************************/
	var objRegExp = /\(/;
	var strMinus = '';

	//check if negative
	if(objRegExp.test(strValue)){
		strMinus = '-';
	}

	objRegExp = /\)|\(|[,]/g;
	strValue = strValue.replace(objRegExp,'');
	if(strValue.indexOf('$') >= 0){
		strValue = strValue.substring(1, strValue.length);
	}
	return strMinus + strValue;
}

function addCurrency( strValue ) {
	/************************************************
	DESCRIPTION: Formats a number as currency.

	PARAMETERS:
	strValue - Source string to be formatted

	REMARKS: Assumes number passed is a valid
	numeric value in the rounded to 2 decimal
	places.  If not, returns original value.
	*************************************************/
	var objRegExp = /-?[0-9]+\.[0-9]{2}$/;

	if( objRegExp.test(strValue)) {
		objRegExp.compile('^-');
		strValue = addCommas(strValue);
		if (objRegExp.test(strValue)){
			strValue = '(' + strValue.replace(objRegExp,'') + ')';
		}
		return '$' + strValue;
	}
	else
	return strValue;
}

function removeCommas( strValue ) {
	/************************************************
	DESCRIPTION: Removes commas from source string.

	PARAMETERS:
	strValue - Source string from which commas will
	be removed;

	RETURNS: Source string with commas removed.
	*************************************************/
	var objRegExp = /,/g; //search for commas globally

	//replace all matches with empty strings
	return strValue.replace(objRegExp,'');
}

function addCommas( strValue ) {
	/************************************************
	DESCRIPTION: Inserts commas into numeric string.

	PARAMETERS:
	strValue - source string containing commas.

	RETURNS: String modified with comma grouping if
	source was all numeric, otherwise source is
	returned.

	REMARKS: Used with integers or numbers with
	2 or less decimal places.
	*************************************************/
	var objRegExp  = new RegExp('(-?[0-9]+)([0-9]{3})');

	//check for match to search criteria
	while(objRegExp.test(strValue)) {
		//replace original string with first group match,
		//a comma, then second group match
		strValue = strValue.replace(objRegExp, '$1,$2');
	}
	return strValue;
}

function removeCharacters( strValue, strMatchPattern ) {
	/************************************************
	DESCRIPTION: Removes characters from a source string
	based upon matches of the supplied pattern.

	PARAMETERS:
	strValue - source string containing number.

	RETURNS: String modified with characters
	matching search pattern removed

	USAGE:  strNoSpaces = removeCharacters( ' sfdf  dfd',
	'\s*')
	*************************************************/
	var objRegExp =  new RegExp( strMatchPattern, 'gi' );

	//replace passed pattern matches with blanks
	return strValue.replace(objRegExp,'');
}


/*
Common expressions

Date
/^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{4}$/     mm/dd/yyyy
US zip code
/(^\d{5}$)|(^\d{5}-\d{4}$)/             99999 or 99999-9999
Canadian postal code
/^\D{1}\d{1}\D{1}\-?\d{1}\D{1}\d{1}$/   Z5Z-5Z5 orZ5Z5Z5
Time
/^([1-9]|1[0-2]):[0-5]\d(:[0-5]\d(\.\d{1,3})?)?$/   HH:MM or HH:MM:SS or HH:MM:SS.mmm
IP Address(no check for alid values (0-255))
/^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/ 999.999.999.999
Dollar Amount
/^((\$\d*)|(\$\d*\.\d{2})|(\d*)|(\d*\.\d{2}))$/ 100, 100.00, $100 or $100.00
Social Security Number
/^\d{3}\-?\d{2}\-?\d{4}$/   999-99-9999 or999999999
Canadian Social Insurance Number
/^\d{9}$/ 999999999

*/

document.getElementsByClassName = function(clsName){
	var retVal = new Array();
	var elements = document.getElementsByTagName('*');
	for(var i=0; i < elements.length; i++){
		if(elements[i].className.indexOf(' ') >= 0){
			var classes = elements[i].className.split(' ');
			for(var j = 0;j < classes.length;j++){
				if(classes[j] == clsName)
				retVal.push(elements[i]);
			}
		}
		else if(elements[i].className == clsName)
		retVal.push(elements[i]);
	}
	return retVal;
}

// return the value of the radio button that is checked
// return an empty string if none are checked, or
// there are no radio buttons
function getCheckedValue(radioObj) {
	if(!radioObj)
	return "";
	var radioLength = radioObj.length;
	if(radioLength == undefined)
	if(radioObj.checked)
	return radioObj.value;
	else
	return "";
	for(var i = 0; i < radioLength; i++) {
		if(radioObj[i].checked) {
			return radioObj[i].value;
		}
	}
	return "";
}

// set the radio button with the given value as being checked
// do nothing if there are no radio buttons
// if the given value does not exist, all the radio buttons
// are reset to unchecked
function setCheckedValue(radioObj, newValue) {
	if(!radioObj)
	return;
	var radioLength = radioObj.length;
	if(radioLength == undefined) {
		radioObj.checked = (radioObj.value == newValue.toString());
		return;
	}
	for(var i = 0; i < radioLength; i++) {
		radioObj[i].checked = false;
		if(radioObj[i].value == newValue.toString()) {
			radioObj[i].checked = true;
		}
	}
}


// Date Validation Javascript
// copyright 30th October 2004, by Stephen Chapman
// http://javascript.about.com/library/bldate.htm

// You have permission to copy and use this javascript provided that
// the content of the script is not changed in any way.

// date_field: '8/24/06'
// format_field: 'U'(M D Y), 'W'(D M Y),'J'(Y M D)
// range_field: 'P'(Past), 'A'(Any), 'F'(Future)
// validateDate(date_field,format_field,range_field)

function valDateFmt(datefmt) {
	myOption = -1;
	for (i=0; i<datefmt.length; i++) {if (datefmt[i].checked) {myOption = i;}}
	if (myOption == -1) {alert("You must select a date format");return ' ';}
	return datefmt[myOption].value;
}
function valDateRng(daterng) {
	myOption = -1;
	for (i=0; i<daterng.length; i++) {if (daterng[i].checked) {myOption = i;}}
	if (myOption == -1) {alert("You must select a date range");return ' ';}
	return daterng[myOption].value;
}
function stripBlanks(fld) {
	var result = "";for (i=0; i<fld.length; i++) {
		if (fld.charAt(i) != " " || c > 0) {result += fld.charAt(i);
		if (fld.charAt(i) != " ") c = result.length;}}return result.substr(0,c);
}
var numb = '0123456789';
function isValid(parm,val) {
	if (parm == "") return true;
	for (i=0; i<parm.length; i++) {if (val.indexOf(parm.charAt(i),0) == -1)
	return false;}return true;
}
function isNum(parm) {
	return isValid(parm,numb);
}
var mth = new Array(' ','january','february','march','april','may','june','july','august','september','october','november','december');
var day = new Array(31,28,31,30,31,30,31,31,30,31,30,31);

function validateDate(fld,fmt,rng) {
	var dd, mm, yy;var today = new Date;var t = new Date;fld = stripBlanks(fld);
	if (fld == '') return false;var d1 = fld.split('\/');
	if (d1.length != 3) d1 = fld.split(' ');
	if (d1.length != 3) return false;
	if (fmt == 'u' || fmt == 'U') {
		dd = d1[1]; mm = d1[0]; yy = d1[2];
	}
	else if (fmt == 'j' || fmt == 'J') {
		dd = d1[2]; mm = d1[1]; yy = d1[0];
	}
	else if (fmt == 'w' || fmt == 'W') {
		dd = d1[0]; mm = d1[1]; yy = d1[2];
	}
	else return false;
	var n = dd.lastIndexOf('st');
	if (n > -1) dd = dd.substr(0,n);
	n = dd.lastIndexOf('nd');
	if (n > -1) dd = dd.substr(0,n);
	n = dd.lastIndexOf('rd');
	if (n > -1) dd = dd.substr(0,n);
	n = dd.lastIndexOf('th');
	if (n > -1) dd = dd.substr(0,n);
	n = dd.lastIndexOf(',');
	if (n > -1) dd = dd.substr(0,n);
	n = mm.lastIndexOf(',');
	if (n > -1) mm = mm.substr(0,n);
	if (!isNum(dd)) return false;
	if (!isNum(yy)) return false;
	if (!isNum(mm)) {
		var nn = mm.toLowerCase();
		for (var i=1; i < 13; i++) {
			if (nn == mth[i] ||
			nn == mth[i].substr(0,3)) {mm = i; i = 13;}
		}
	}
	if (!isNum(mm)) return false;
	dd = parseFloat(dd); mm = parseFloat(mm); yy = parseFloat(yy);
	if (yy < 100) yy += 2000;
	if (yy < 1582 || yy > 4881) return false;
	if (mm == 2 && (yy%400 == 0 || (yy%4 == 0 && yy%100 != 0))) day[mm-1]++;
	if (mm < 1 || mm > 12) return false;
	if (dd < 1 || dd > day[mm-1]) return false;
	t.setDate(dd); t.setMonth(mm-1); t.setFullYear(yy);
	if (rng == 'p' || rng == 'P') {
		if (t > today) return false;
	}
	else if (rng == 'f' || rng == 'F') {
		if (t < today) return false;
	}
	else if (rng != 'a' && rng != 'A') return false;
	return true;
}

// http://www.suite101.com/article.cfm/javascript/61279
// Date format: mm/dd/yy where '/' is a separator
// The function will compare the dates and return:
// 	1 if the first one is a later date
// 	-1 if the first one is an earlier date
// 	0 if the dates are same
function compareDates (value1, value2) {
	var date1, date2;
	var month1, month2;
	var year1, year2;

	month1 = value1.substring (0, value1.indexOf ("/"));
	date1 = value1.substring (value1.indexOf ("/")+1, value1.lastIndexOf ("/"));
	year1 = value1.substring (value1.lastIndexOf ("/")+1, value1.length);

	month2 = value2.substring (0, value2.indexOf ("/"));
	date2 = value2.substring (value2.indexOf ("/")+1, value2.lastIndexOf ("/"));
	year2 = value2.substring (value2.lastIndexOf ("/")+1, value2.length);

	if (parseInt(year1) > parseInt(year2)) return 1;
	else if (parseInt(year1,10) < parseInt(year2,10)) return -1;
	else if (parseInt(month1,10) > parseInt(month2,10)) return 1;
	else if (parseInt(month1,10) < parseInt(month2,10)) return -1;
	else if (parseInt(date1,10) > parseInt(date2,10)) return 1;
	else if (parseInt(date1,10) < parseInt(date2,10)) return -1;
	else return 0;
}

// http://www.webdeveloper.com/forum/archive/index.php/t-20249.html
// Autoformat Social Security Number in format: 999-99-9999
function autoformatSSN(ssn) {
	re = /\D/g; // remove any characters that are not numbers
	socnum=ssn.value.replace(re,"")
	sslen=socnum.length
	if(sslen>3&&sslen<6)
	{
		ssa=socnum.slice(0,3)
		ssb=socnum.slice(3,5)
		ssn.value=ssa+"-"+ssb
	}
	else
	{
		if(sslen>5)
		{
			ssa=socnum.slice(0,3)
			ssb=socnum.slice(3,5)
			ssc=socnum.slice(5,9)
			ssn.value=ssa+"-"+ssb+"-"+ssc
		}
		else
		{ssn.value=socnum}
	}
}

// (XXX)XXX-XXXX
// (508)325-5739
function formatPhone(myfield)
{
	if(myfield.length<10) {
		var pad = 10-myfield.length;
		for(var i=0; i<pad; i++) {
			myfield += '1';
		}
	}

	var p=/^([\d]{3})([\d]{3})([\d]{4,})$/.exec(myfield);
	return "("+p[1]+")"+p[2]+"-"+p[3];
}

// http://www.htmlcodetutorial.com/forms/index_famsupp_158.html
// USAGE: <input name="dollar" size=5 maxlength="5" onKeyPress="return numbersonly(this, event);">
function numbersonly(myfield, e, dec)
{
	var key;
	var keychar;

	if (window.event)
	key = window.event.keyCode;
	else if (e)
	key = e.which;
	else
	return true;
	keychar = String.fromCharCode(key);

	// control keys
	if ((key==null) || (key==0) || (key==8) ||
	(key==9) || (key==13) || (key==27) )
	return true;

	// numbers
	else if ((("0123456789").indexOf(keychar) > -1))
	return true;

	// decimal point jump
	else if (dec && (keychar == "."))
	{
		//myfield.form.elements[dec].focus();
		if(myfield.value.indexOf('.') > 0)	
		return false;
	}
	else
	return false;
}

// USAGE: <input name="dollar" size=5 maxlength="5" onkeypress="return floatsonly(this, event);">
function floatsonly(myfield, e) {
	var key;
	var keychar;

	if (window.event)
	key = window.event.keyCode;
	else if (e)
	key = e.which;
	else
	return true;
	keychar = String.fromCharCode(key);

	// control keys
	if ((key==null) || (key==0) || (key==8) ||
	(key==9) || (key==13) || (key==27) )
	return true;

	// numbers
	else if ((("-0123456789.").indexOf(keychar) > -1))
	return true;

	else
	return false;
}

// Usage: DisableEnableForm(true); 	// Disable
// DisableEnableForm(false); 		// Enable
function DisableEnableForm(xHow){
	var objForms = document.forms;
	for(k=0; k<objForms.length; k++) {
		objElems = objForms[k].elements;
		for(i=0; i<objElems.length; i++) {
			objElems[i].disabled = xHow;
		}
	}
}

// Usage: <button onclick="insertAtCursor(document.getElementById('myText'),'Test Text')">Add Test Text</button>
//myField accepts an object reference, myValue accepts the text strint to add
function insertAtCursor(myField, myValue) {
	//IE support
	if (document.selection) {
		myField.focus();

		//in effect we are creating a text range with zero
		//length at the cursor location and replacing it
		//with myValue
		sel = document.selection.createRange();
		sel.text = myValue;
	}

	//Mozilla/Firefox/Netscape 7+ support
	else if (myField.selectionStart || myField.selectionStart == '0') {

		//Here we get the start and end points of the
		//selection. Then we create substrings up to the
		//start of the selection and from the end point
		//of the selection to the end of the field value.
		//Then we concatenate the first substring, myValue,
		//and the second substring to get the new value.
		var startPos = myField.selectionStart;
		var endPos = myField.selectionEnd;
		myField.value = myField.value.substring(0, startPos)+ myValue+ myField.value.substring(endPos, myField.value.length);
	} else {
		myField.value += myValue;
	}
}
// Usage: DisableEnableForm(true); 	// Disable
// DisableEnableForm(false); 		// Enable
function DisableEnableForm(xHow){
	var objForms = document.forms;
	for(k=0; k<objForms.length; k++) {
		objElems = objForms[k].elements;
		for(i=0; i<objElems.length; i++) {
			objElems[i].disabled = xHow;
		}
	}
}

// http://techpatterns.com/downloads/javascript_cookies.php
function Set_Cookie( name, value, expires, path, domain, secure )
{
	// set time, it's in milliseconds
	var today = new Date();
	today.setTime( today.getTime() );

	/*
	if the expires variable is set, make the correct
	expires time, the current script below will set
	it for x number of days, to make it for hours,
	delete * 24, for minutes, delete * 60 * 24
	*/
	if ( expires )
	{
		expires = expires * 1000 * 60 * 60 * 24;
	}
	var expires_date = new Date( today.getTime() + (expires) );

	document.cookie = name + "=" +escape( value ) +
	( ( expires ) ? ";expires=" + expires_date.toGMTString() : "" ) +
	( ( path ) ? ";path=" + path : "" ) +
	( ( domain ) ? ";domain=" + domain : "" ) +
	( ( secure ) ? ";secure" : "" );
}

// this fixes an issue with the old method, ambiguous values
// with this test document.cookie.indexOf( name + "=" );
function Get_Cookie( check_name ) {
	// first we'll split this cookie up into name/value pairs
	// note: document.cookie only returns name=value, not the other components
	var a_all_cookies = document.cookie.split( ';' );
	var a_temp_cookie = '';
	var cookie_name = '';
	var cookie_value = '';
	var b_cookie_found = false; // set boolean t/f default f

	for ( i = 0; i < a_all_cookies.length; i++ )
	{
		// now we'll split apart each name=value pair
		a_temp_cookie = a_all_cookies[i].split( '=' );


		// and trim left/right whitespace while we're at it
		cookie_name = a_temp_cookie[0].replace(/^\s+|\s+$/g, '');

		// if the extracted name matches passed check_name
		if ( cookie_name == check_name )
		{
			b_cookie_found = true;
			// we need to handle case where cookie has no value but exists (no = sign, that is):
			if ( a_temp_cookie.length > 1 )
			{
				cookie_value = unescape( a_temp_cookie[1].replace(/^\s+|\s+$/g, '') );
			}
			// note that in cases where cookie is initialized but no value, null is returned
			return cookie_value;
			break;
		}
		a_temp_cookie = null;
		cookie_name = '';
	}
	if ( !b_cookie_found )
	{
		return null;
	}
}

// this deletes the cookie when called
function Delete_Cookie( name, path, domain ) {
	if ( Get_Cookie( name ) ) document.cookie = name + "=" +
	( ( path ) ? ";path=" + path : "") +
	( ( domain ) ? ";domain=" + domain : "" ) +
	";expires=Thu, 01-Jan-1970 00:00:01 GMT";
}

function ucfirst(el) {
	if(el.value != "") {
		var str = el.value;
		el.value = str.charAt(0).toUpperCase() + str.slice(1);
	}
}