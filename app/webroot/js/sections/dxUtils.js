var globalWindow = null;
var valX = 0;
var valY = 0;

var YUIWindow = null;
var initYUI = false;
var YUIHeader = "";


var _buttonArray;
var _JqHandle;
var _isIPad = navigator.userAgent.match(/iPad/i) != null;
var _focusOnTable = false;

//
// dxUtils.js
// general javascript utilities.
// NOTE:  As this file grows, it should be broken up and placed into a jar file.
//

function locOf(item)
{
    //recursively finds an item's location on the page (IE)
    valX += item.offsetLeft;
    valY += item.offsetTop;
    locParent = item.offsetParent;
    if (locParent) locOf(locParent);
}

// targetControl is the item you wish to populate
// widget is your (MyPreferredList) widget
function setPreferredListItem(targetControl, widget)
{
    var optIndex = widget.selectedIndex;
    if (optIndex)
    {
        var text = widget.options[optIndex].text;
        targetControl.value = text;
    }
} //setPreferredListItem()



// set a textfield's value to the current date in mm/dd/yyyy format
// widget is the text control you wish to populate.
function setTodaysDate(widget)
{
    var today = new Date();
    widget.value = today.getMonth()+1 + "/" + today.getDate() + "/" + today.getFullYear();
    return true;
} //setTodaysDate()


// This function will populate widgets with the appropriate time.
// widget is the time textfield, ampm is the AM/PM select control.
// the ampm control is optional, pass in null if not defined.
function setCurrentTime(widget, ampm)
{
    var today = new Date();
    var hours = today.getHours();
    var pm = false;
    if (hours >= 12)
    {
        pm = true;
    }
    if (hours > 12)
    {
        hours = hours - 12;
    }

    if (hours == 0)
    {
        hours = 12;
    }
    else if (hours < 10)
    {
        hours = "0" + hours;
    }

    var minutes = today.getMinutes();
    if (minutes < 10)
    {
        minutes = "0" + minutes;
    }
    time = hours + ":" + minutes;
    widget.value = time;

    //SCR 13251
    var iPM;
    var iAM;

    if (ampm != null)
    {
        for (var j = 0; j < ampm.options.length; j ++)
        {
            var text = ampm.options[j].text;
            if (text == "PM")
            {
                iPM = j;
            }
            if (text == "AM")
            {
                iAM = j;
            }
        }
    }



    if (ampm != null)
    {
        if (pm)
            ampm.selectedIndex = iPM;
        else
            ampm.selectedIndex = iAM;
    }
    return true;
} //setCurrentTime()

//SCR 8380
// This function will deletermine if ampm based on the Current time
// AM or PM
function setAMPM(ampm)
{
    var today = new Date();
    var hours = today.getHours();
    var pm = false;
    if (hours >= 12)
    {
        ampm.value = "PM";
    }
    else
    {
        ampm.value = "AM";
    }

    return true;
} //setCurrentTime()


function getCurrentTime()
{
    var today = new Date();
    var hours = today.getHours();
    var pm = false;
    if (hours >= 12)
    {
        pm = true;
    }
    if (hours > 12)
    {
        hours = hours - 12;
    }

    if (hours == 0)
    {
        hours = 12;
    }
    else if (hours < 10)
    {
        hours = "0" + hours;
    }

    var minutes = today.getMinutes();
    if (minutes < 10)
    {
        minutes = "0" + minutes;
    }
    time = hours + ":" + minutes + " ";

    if (pm)
        time += "PM";
    else
        time += "AM";

    document.write(time);
} //getCurrentTime()

// check a form for an empty field
// return true if the field is empty
function checkForEmptyField(field)
{
    var invalidField = false;

    if (field.type == "text" || field.type == "hidden" || field.type == "textarea")
    {
        if (field.value == "")
            invalidField = true;
    }
    else if ((field.type == "select-one") || (field.type == "select-multiple"))
    {
        var optIndex = field.selectedIndex;
        if (optIndex >= 0)
        {
            var text = field.options[optIndex].text;
            if ((text == "") || (text == null))
                invalidField = true;
        }
        else
        {
            invalidField = true;
        }
    }

    return invalidField;
} //checkForEmptyField()

// function to handle page unload event
function onUnload()
{
}


<!--***************** Formatting and Validation ***************************** -->

function stripFormatChar(s, cStripAfter)
{
    fp = "";
    iPeriodIndex = s.indexOf(cStripAfter);
    while (iPeriodIndex >= 0)
    {
        fp = fp + s.substring(0, iPeriodIndex);
        s = s.substring(iPeriodIndex+1, s.length);
        iPeriodIndex = s.indexOf(cStripAfter);
    }
    return fp+s;
}

function stripNonNumber(s)
{
    var sNumber = "";
    var i;
    for (i = 0; i < s.length; i++)
    {
        if (s.charAt(i) >= '0' && s.charAt(i) <= '9')
        {
            sNumber = sNumber + s.charAt(i);
        }
    }

    return sNumber;
}


function displayMoney(targetControl)
{
    if (targetControl.value == "" || targetControl.value == null)
    {
        return true;
    }

    p = targetControl.value;

    var s = "";
    var i;

    for (i = 0; i < p.length; i++)
    {
        if ((p.charAt(i) >= '0' && p.charAt(i) <= '9') || p.charAt(i) == '.')
        {
            s = s + p.charAt(i);
        }
    }

    if (s.length > 0)
    {
        targetControl.value = s;
    }
    else
    {
        targetControl.value = "";
    }

    var dollarsExp = /^\d+(\.\d{2})?$/;
    var dollarsCentsExp = /^\d+\.\d\d$/;

    result = dollarsExp.test(targetControl.value);

    if (!result)
    {
        alert(targetControl.value + " is not a valid amount. Enter like 50 or 50.00");
        if (!targetControl.disabled)
        {
            targetControl.focus();
            targetControl.select();
        }
        return false;
    }
    return result;
}



function displayPhone(targetControl)
{

    if (targetControl.value == "" || targetControl.value == null)
        return true;

    p = targetControl.value;
    //strip if already had formatted characters
    p = stripNonNumber(p);

    if (p.length == 10)
    {
        targetControl.value = "("+p.substring(0, 3) +")"+p.substring(3, 6) + "-"+ p.substring(6, 10);
    }

    var phoneExp = /\(\d\d\d\)\d\d\d\-\d{4}/;
    var result = phoneExp.test(targetControl.value);

    if (!result)
    {
        alert(p + " is not a valid Phone Number. Enter like (123)123-1234.");
        if (!targetControl.disabled)
        {
            targetControl.focus();
            targetControl.select();
        }
        return false;
    }
    return result;

}

function displaySSN(targetControl)
{

    if (targetControl.value == "" || targetControl.value == null)
    {
        return true;
    }

    p = targetControl.value;
    //strip if already had formatted characters
    p = stripFormatChar(p, "-");


    if (p.length > 0 && p.length == 9)
    {
        targetControl.value = p.substring(0, 3) +"-"+p.substring(3, 5) + "-"+ p.substring(5, 9);
    }


    var ssnExp = /\d\d\d\-\d\d\-\d{4}/;
    var result = ssnExp.test(targetControl.value);

    if (!result)
    {
        alert(p + " is not a valid SSN. Enter like 123-12-1234.");
        if (!targetControl.disabled)
        {
            targetControl.focus();
            targetControl.select();
        }
        return false;
    }
    return result;
}


function displayOrderNumber(targetControl)
{
    var result = true;

    if (targetControl.value == "" || targetControl.value == null)
        return true;
    var numExp;
    var p = targetControl.value.length;
    if (p > 0)
    {
        var n = "\\d{" + p + "}";
        var numExp = new RegExp(n, "");
        result = numExp.test(targetControl.value);
    }

    if (!result)
    {
        alert(targetControl.value + " is not a valid Order Number. Accepts only numeric.");
        if (!targetControl.disabled)
        {
            targetControl.focus();
            targetControl.select();
        }
        return false;
    }
    return result;
}

function displayZip(targetControl)
{

    var hasFive = true;

    var zip5Exp = /\d\d\d\d\d/;
    var zip9Exp = /\d\d\d\d\d-\d\d\d\d/;

    if (targetControl.value == "" || targetControl.value == null)
        return true;

    p = targetControl.value;

    //strip if already had formatted characters
    p = stripNonNumber(p);

    var result = true;

    if (p.length > 0 && p.length == 5)
    {
        targetControl.value = p.substring(0, 5);
        hasFive = true;
    }
    else if (p.length > 0 && p.length == 9)
    {
        targetControl.value = p.substring(0, 5) +"-"+p.substring(5, 9);
        hasFive = false;
    }
    else if (p.length > 0)
    {
        result = false;
    }


    if (p.length > 0 && p.length > 9)
    {
        result = false;
    }
    else if (hasFive && result)
    {
        result = zip5Exp.test(targetControl.value);
    }
    else if (result)
    {
        result = zip9Exp.test(targetControl.value);
    }

    if (!result)
    {
        alert(p + " is not a valid Zip. Enter like 30328-1234 or 30328.");
        if (!targetControl.disabled)
        {
            targetControl.focus();
            targetControl.select();
        }
        return false;
    }
    return result;
}

function checkYear(year)
{
    if (year.length < 4 || year.length > 4)
        return false;
    if (year.length == 4 && year == "0000")
        return false;
    return true;
}

//no check for leap year...will come later!!!
function checkDay(day, mm, bLeap)
{
    //strip 0 if leading
    if (day.length == 2 && day.substring(0, 1) == "0")
    {
        day = day.substring(1, 2);
    }

    //strip 0 if leading
    if (mm.length == 2 && mm.substring(0, 1) == "0")
    {
        mm = mm.substring(1, 2);
    }


    month = parseInt(mm);
    d = parseInt(day);


    if (d == 0)
    {
        alert("Invalid Day. Day cannot be zero.");
        return false;
    }

    switch (month)
    {
        //Jan/March/May/June/July/August/Oct/Dec
        case (1):
        case (3):
        case (5):
        case (7):
        case (8):
        case (10):
        case (12):
            if (day > 31)
            {
                alert("Invalid Day. Day must be in range 1 - 31.");
                return false;
            }
            break;
            //Feb
        case (2):
        {
            if ((day > 29) && bLeap)
            {
                alert("Invalid Day. Day must be in range 1 - 29.");
                return false;
            }
            else if ((day > 28) && !bLeap)
            {
                alert("Invalid Day. Day must be in range 1 - 28.");
                return false;
            }
            break;
        }

        //April/June/Sept/Nov
        case (4):
        case (6):
        case (9):
        case (11):
            if (day > 30)
            {
                alert("Invalid Day. Day must be in range 1 - 30.");
                return false;
            }
            break;
    } //end of switch

    return true;
}


function checkMonth(month)
{
    //strip 0 if leading
    if (month.length == 2 && month.substring(0, 1) == "0")
    {
        month = month.substring(1, 2);
    }
    m = parseInt(month);
    if (m > 0 && m <= 12)
        return true;
    return false;
}

//method that checks if the user entered a future date
function checkFutureDate(control)
{
    return checkFutureDate(control, "Date cannot be a future date.");
}


//method that checks if the user entered a future date
function checkDateRange(fromDate, toDate)
{
    var result = true;

    //strip if already had formatted characters
    var i = fromDate.value.split("/");
    var j = toDate.value.split("/");
    if (i.length == 3 && j.length == 3)
    {
        mon = i[0];
        day = i[1];
        year = i[2];

        mon = stripLeadingCharacter(mon, "0");
        day = stripLeadingCharacter(day, "0");


        mon2 = j[0];
        day2 = j[1];
        year2 = j[2];

        mon2 = stripLeadingCharacter(mon2, "0");
        day2 = stripLeadingCharacter(day2, "0");


        //check year first
        if (parseInt(year) > parseInt(year2))
        {
            result = false;
        }

        if (parseInt(year2) <= parseInt(year) && parseInt(mon) > parseInt(mon2))
        {
            result = false;
        }
        if (parseInt(year2) <= parseInt(year) && parseInt(mon2) <= parseInt(mon) &&
            parseInt(day) > parseInt(day2))
        {
            result = false;
        }

    }

    if (!result)
    {
        alert("From date cannot be greater than to date.");
        if (!fromDate.disabled)
        {
            fromDate.focus();
            fromDate.select();
        }
    }
    return result;
}



function stripLeadingCharacter(data, c)
{
    iZeroIndex = data.indexOf(c);
    if (iZeroIndex == 0)
    {
        data = data.substring(iZeroIndex+1, data.length);
    }
    return data;
}

// format date of birth field
function displayBirthDate(control)
{
    var result = true;
    if (displayDate(control))
    {
        //strip if already had formatted characters
        var i = control.value.split("/");
        if (i.length == 3)
        {
            mon = i[0];
            day = i[1];
            year = i[2];


            mon = stripLeadingCharacter(mon, "0");
            day = stripLeadingCharacter(day, "0");


            //check if not a future date
            dd = new Date();
            d = dd.getDate();
            m = parseInt(dd.getMonth()) + 1;
            y = dd.getFullYear();

            //check year first
            if (parseInt(year) > y)
            {
                result = false;
            }

            if (y <= parseInt(year) && parseInt(mon) > m)
            {
                result = false;
            }
            if (y <= parseInt(year) && m <= parseInt(mon) &&
                parseInt(day) > d)
            {
                result = false;
            }

        }
    }
    else
        return false;
    if (!result)
    {
        alert("Birth Date cannot be a future date.");
        if (!control.disabled)
        {
            control.focus();
            control.select();
        }
    }
    return result;
}

// format time field
function displayTime(control)
{

    // RegExp to test for "hh:mm"
    var timeExp = /\d{1,2}\:?\d\d/;

    var result = true;

    if (control.value == "" || control.value == null)
    {
        return true;
    }

    //in case the user entered like 405 it will display 04:05
    if (control.value.length > 0 && control.value.length == 3)
    {
        control.value = "0" + control.value;
    }

    //in case the user entered like 4:05 it will display 04:05
    if (control.value.length > 0 && control.value.length == 4)
    {
        if (control.value.indexOf(":") != -1)
        {
            control.value = "0" + control.value;
        }
    }

    result = timeExp.test(control.value);
    if (!result)
    {
        alert("Invalid time. Please enter a value like 12:00.");
        if (!control.disabled)
        {
            control.focus();
            control.select();
        }
        return false;
    }

    p = control.value;
    //strip if already had formatted characters
    p = stripFormatChar(p, ":");

    var hh = "";
    var mm = "";



    if (p.length > 0 && p.length >= 3)
    {
        if (p.length == 3)
        {
            hh = p.substring(0, 1);
            mm = p.substring(1, 3);
        }
        else
        {
            hh = p.substring(0, 2);
            mm = p.substring(2, 4);
        }

        //strip 0 if leading for hours
        if (hh.length == 2 && hh.substring(0, 1) == "0")
        {
            hh = hh.substring(1, 2);
        }

        hour = parseInt(hh);
        min = parseInt(mm);
        if (hour > 12 || hour < 1)
        {
            result = false;
        }
        if (min >= 60)
        {
            result = false;
        }
    }
    else
    {
        result = false;
    }

    if (result)
        control.value = hh + ":" + mm;
    else
    {
        alert("Invalid time. Please enter a value like 12:00.");
        if (!control.disabled)
        {
            control.focus();
            control.select();
        }
    }
    return result;
}


//method that checks if the user entered a future date
function checkDateRange(fromDate, toDate, fld, msg)
{
    var result = true;

    //strip if already had formatted characters
    var i = fromDate.value.split("/");
    var j = toDate.value.split("/");
    if (i.length == 3 && j.length == 3)
    {
        mon = i[0];
        day = i[1];
        year = i[2];

        mon = stripLeadingCharacter(mon, "0");
        day = stripLeadingCharacter(day, "0");


        mon2 = j[0];
        day2 = j[1];
        year2 = j[2];

        mon2 = stripLeadingCharacter(mon2, "0");
        day2 = stripLeadingCharacter(day2, "0");


        //check year first
        if (parseInt(year) > parseInt(year2))
        {
            result = false;
        }

        if (parseInt(year2) <= parseInt(year) && parseInt(mon) > parseInt(mon2))
        {
            result = false;
        }
        if (parseInt(year2) <= parseInt(year) && parseInt(mon2) <= parseInt(mon) &&
            parseInt(day) > parseInt(day2))
        {
            result = false;
        }

    }

    if (!result)
    {
        alert(msg);
        if (!fld.disabled)
        {
            fld.focus();
            fld.select();
        }
    }
    return result;
}



// format date of birth field
function checkFutureDate(control, msg)
{
    var result = true;
    if (displayDate(control))
    {
        //strip if already had formatted characters
        var i = control.value.split("/");
        if (i.length == 3)
        {
            imon = i[0];
            iday = i[1];
            iyear = i[2];

            imon = stripLeadingCharacter(imon, "0");
            iday = stripLeadingCharacter(iday, "0");

            //check if not a future date
            dd = new Date();
            d = dd.getDate();
            m = parseInt(dd.getMonth()) + 1;
            y = dd.getFullYear();

            //check year first
            if (parseInt(iyear) > y)
            {
                result = false;
            }

            if (y <= parseInt(iyear) && parseInt(imon) > m)
            {
                result = false;
            }
            if (y <= parseInt(iyear) && m <= parseInt(imon) &&
                parseInt(iday) > d)
            {
                result = false;
            }
        }
    }
    else
        return false;
    if (!result)
    {
        alert(msg);
        if (!control.disabled)
        {
            control.focus();
            control.select();
        }
    }
    return result;
}

function isNumeric(sText)
{
    var ValidChars = "0123456789";
    var IsNumber = true;
    var Char;


    for (i = 0; i < sText.length && IsNumber == true; i++)
    {
        Char = sText.charAt(i);
        if (ValidChars.indexOf(Char) == -1)
        {
            IsNumber = false;
        }
    }
    return IsNumber;

}


// format date field
function displayDate(control)
{
    yy = "";
    mm = "";
    dd = "";

    // RegExp to test for "mm/dd/yyyy"
    var dateSExp = /\d\d?\/\d\d?\/\d{4}/;

    var result = true;

    if (control.value == "" || control.value == null)
        return true;

    p = control.value;


    // T - Current Date
    // T-1 will show yesterday's date
    // T+1 will show tomorrow's date etc..
    if ((p.indexOf("T") != -1) || (p.indexOf("t") != -1))
    {
        if (p.indexOf("-") != -1)
        {
            var tt = p.split("-");
            if (isNumeric(tt[1]))
            {
                var myDate = new Date();
                myDate.setDate(myDate.getDate()-parseInt(tt[1]));
                control.value = myDate.getMonth()+1 + "/" + myDate.getDate() + "/" + myDate.getFullYear();
                return;
            }
        }
        else if (p.indexOf("+") != -1)
        {
            var idate = p.split("+");
            if (isNumeric(idate[1]))
            {
                var myDate2 = new Date();
                myDate2.setDate(myDate2.getDate()+parseInt(idate[1]));
                control.value = myDate2.getMonth()+1 + "/" + myDate2.getDate() + "/" + myDate2.getFullYear();
                return;
            }
        }
        else if (p == "T" || p == "t")
        {
            setTodaysDate(control);
            return;
        }
    }


    //strip if already had formatted characters
    var i = control.value.split("/");
    if (i.length == 3)
    {
        mm = i[0];
        dd = i[1];
        yy = i[2];
    }
    else
    {
        i = control.value.split("-");
        if (i.length == 3)
        {
            mm = i[0];
            dd = i[1];
            yy = i[2];
        }
        else
        {
            //check if user enter - in the format

            //strip if already had formatted characters
            p = stripFormatChar(p, "/");
            p = stripFormatChar(p, "-");


            if (p.length > 0 && p.length < 8)
            {
                alert("Incorrect date format.  Please use (mm/dd/yyyy).");
                control.select();
                return false;
            }
            else if (p.length == 10)
            {
                alert("Incorrect date format.  Please use (mm/dd/yyyy).");
                if (!control.disabled)
                {
                    control.focus();
                    control.select();
                }
                return false;
            }
            else if (p.length > 0 && p.length == 8)
            {
                mm = p.substring(0, 2);
                dd = p.substring(2, 4);
                yy = p.substring(4, p.length);
                //SCR 13492
                if (yy.length < 4 || yy.length > 4)
                {
                    alert("Incorrect date format.  Please use (mm/dd/yyyy).");
                    if (!control.disabled)
                    {
                        control.focus();
                        control.select();
                    }
                    return false;
                }
            }
            else
            {
                alert("Incorrect date format.  Please use (mm/dd/yyyy).");
                if (!control.disabled)
                {
                    control.focus();
                    control.select();
                }
                return false;
            }

        }
    }

    //validate mm yy dd
    if (yy != "" && !checkYear(yy))
    {
        alert("Invalid Year.");
        if (!control.disabled)
        {
            control.focus();
            control.select();
        }
        return false;
    }

    var bCentury = true;
    var bLeap = true;
    //is the year a leap year
    //check if century year
    if ((parseInt(yy) % 100) == 0)
    {
        bCentury = true;
    }
    else
    {
        bCentury = false;
    }
    //check if leap year
    if (bCentury)
    {
        if ((parseInt(yy) % 400) == 0)
        {
            bLeap = true;
        }
        else
        {
            bLeap = false;
        }
    }
    else
    {
        if ((parseInt(yy) % 4) == 0)
        {
            bLeap = true;
        }
        else
        {
            bLeap = false;
        }
    }


    if (!checkMonth(mm))
    {
        alert("Invalid Month.  Must be between 1-12.");
        if (!control.disabled)
        {
            control.focus();
            control.select();
        }
        return false;
    }
    if (!checkDay(dd, mm, bLeap))
    {
        if (!control.disabled)
        {
            control.focus();
            control.select();
        }
        return false;
    }


    p = mm + "/" + dd + "/" + yy;
    control.value = p;

    result = dateSExp.test(control.value);

    if (!result)
    {
        alert("Incorrect date format.  Please use (mm/dd/yyyy).");
        if (!control.disabled)
        {
            control.focus();
            control.select();
        }
        return false;
    }
    return result;
}




<!--*****************End Of Formatting and Validation ***************************** -->

function encodeURL (sURL)
{
    var sEncodedURL = sURL;

    var nParams = sURL.indexOf("?");
    if (nParams != -1)
    {
        var sHead = sURL.substring(0, nParams+1);
        var aParams = sURL.substring(nParams+1).split("&");
        var sParams = "";
        for (var i = 0; i < aParams.length; i++)
        {
            var sParam = aParams[i];
            var nEq = sParam.indexOf("=");
            var sName = sParam.substring(0, nEq+1);
            var sValue = sParam.substring(nEq+1);
            sParams += sName + escape(sValue);
            if (i+1 < aParams.length)
            {
                sParams += "&";
            }
        }
        sEncodedURL = sHead + sParams;
    }

    return sEncodedURL;
}


function openWin(winName, page, width, height, hasMenu)
{
    var x = Math.round(document.body.clientWidth/4);
    var y = Math.round(document.body.clientHeight/4);
    var centerX = Math.round(screen.availWidth/2);
    var centerY = Math.round(screen.availHeight/2);

    try
    {
        // if window open, then close it.
        if (globalWindow != null)
            globalWindow.close();
    }
    catch (e)
    {
    } // if it doesn't work, the window is already closed anyway

    if (arguments.length == 2)
    {
        x = Math.round(centerX - (750/2));
        y = Math.round(centerY - (500/2));
        globalWindow = window.open(encodeURL(page),
            winName,
            "scrollbars,height=500,width=750,resizable,left="+x+",top="+y);
    }
    else
    {
        x = Math.round(centerX - (width/2));
        y = Math.round(centerY - (height/2));
        globalWindow = window.open(encodeURL(page),
            winName,
            "scrollbars,height="+height+",width="+width+",resizable,left="+x+",top="+y);
    }
    if (globalWindow != null)
    {
        globalWindow.focus();
    }
    return globalWindow;
} //openWin()



function openWinMenu(winName, page, width, height)
{
    var x = Math.round(document.body.clientWidth/4);
    var y = Math.round(document.body.clientHeight/4);
    var centerX = Math.round(screen.availWidth/2);
    var centerY = Math.round(screen.availHeight/2);

    try
    {
        // if window open, then close it.
        if (globalWindow != null)
            globalWindow.close();
    }
    catch (e)
    {
    } // if it doesn't work, the window is already closed anyway

    if (arguments.length == 2)
    {
        x = Math.round(centerX - (750/2));
        y = Math.round(centerY - (500/2));
        globalWindow = window.open(encodeURL(page),
            winName,
            "menubar,scrollbars,height=500,width=750,resizable,left="+x+",top="+y);
    }
    else
    {
        x = Math.round(centerX - (width/2));
        y = Math.round(centerY - (height/2));
        globalWindow = window.open(encodeURL(page),
            winName,
            "menubar,scrollbars,height="+height+",width="+width+",resizable,left="+x+",top="+y);
    }
    globalWindow.focus();
    return globalWindow;
} //openWin()


// launch a help window...
function helpWin(target)
{
    if (window.dx_help_win)
        window.dx_help_win.close();
    var helpwin = openWin ("dx_help_win", "/servlet/HelpServlet?id="+target, 1024, 768, false);
    /*
  var helpwin = open("/servlet/HelpServlet?id="+target,"dx_help_win",
         "resizable,scrollbars,left=0,top=0,width="+width+",height="+height);
 */
    helpwin.focus();
}

// launch the corporate help/etc. window..
function openHelp(item)
{
    var target = "/html/errors/PageNotFound.html";
    switch (item)
    {
        case 1: // Practice Help
            target = "/html/corporate/WebMdPracticeHelp.html";
            break;
        case 2: // Glossary
            target = "/html/corporate/Glossary.html";
            break;
        case 3: // Feedback
            target = "/html/corporate/Feedback.html";
            break;
        case 4: // legal notices
            target = "/help/about.htm";
            break;
    }
    win = window.open(target,
        "dx_help_win",
        "scrollbars,left=0,top=0,height=800,width=600,resizable");

} //openHelp()



function displayNumeric(targetControl, lbl)
{
    var result = true;
    if (targetControl.value == "" || targetControl.value == null)
        return true;
    var p = targetControl.value.length;
    if (p > 0)
    {
        var n = "\\d{" + p + "}";
        var numExp = new RegExp(n, "");
        result = numExp.test(targetControl.value);
    }
    if (!result)
    {
        alert(targetControl.value + " is not a valid " + lbl + ". Accepts only numeric.");
        targetControl.select();
        result = false;
    }
    return result;
}

function noLabError(name)

{
    alert("Facility " + name +" does not have a default Lab set,\n\nor the default lab does not have any Client Ids set. Please call the HELP Desk.");
}




function showProcessingGif(control)
{
    if ((typeof jQuery != 'undefined') && $.fn.showLoading)
    {
        $(control).showLoading();
    }
    else
    {
        // show wait gif..
        var wait = null;
        if (document.forms[0].processingGif)
        {
            wait = document.forms[0].processingGif;
        }
        else if (document.all && document.all.processingGif)
        {
            wait = document.all.processingGif;
        }

        if (wait)
        {
            locOf(control); // loads valX, valY
            wait.style.pixelLeft = valX-10;
            wait.style.pixelTop = valY-10;
            wait.style.zIndex = 5;
            wait.style.visibility = "visible";
        }
    }
}

function showSearchingGif(control)
{
    // show processing gif..
    locOf(control); // loads valX, valY
    var wait = null;
    if (document.forms[0].waitGif)
    {
        wait = document.forms[0].waitGif;
    }
    else if (document.all && document.all.waitGif)
    {
        wait = document.all.waitGif;
    }

    if (waitGif)
    {
        wait.style.pixelLeft = valX-10;
        wait.style.pixelTop = valY-10;
        wait.style.zIndex = 5;
        wait.style.visibility = "visible";
    }
}



function displayUrineHours(targetControl)
{

    if (targetControl.value == "" || targetControl.value == null)
        return true;
    var numExp;
    var p = targetControl.value.length;

    if (p > 0)
    {
        switch (p)
        {
            case 1:
                numExp = /\d{1}/;
                break;
            case 2:
                numExp = /\d{2}/;
                break;
        }
    }

    var result = numExp.test(targetControl.value);
    if (!result)
    {
        alert(targetControl.value + " is not a valid Urine Hours. Accepts only numeric");
        targetControl.select();
        result = false;
    }
    else
    {
        //range is between 0 and 72
        n = parseInt(targetControl.value);
        if (n < 0 || n > 72)
        {
            alert(targetControl.value + " is not a valid Urine Hours. Accepts between 0 - 72");
            targetControl.select();
            result = false;

        }

    }
    return result;
}

function validateBlank(field)
{
    var content = "";
    if (field.type == "text" || field.type == "hidden")
    {
        content = field.value;
    }
    else if ((field.type == "select-one") || (field.type == "select-multiple"))
    {
        var optIndex = field.selectedIndex;
        if (optIndex >= 0)
        {
            content = field.options[optIndex].text;
        }
    }

    var isBlank = false;
    var num = 0;
    for (var i = 0; i < content.length; i++)
    {
        //if prefixed with space return true
        if (content.charAt(i) == ' ' && i == 0)
        {
            return true;
        }
        else if (content.charAt(i) == ' ')
        {
            num++;
        }
    }
    /*
 if (num > (content.length/2))
 isBlank = true;*/
    return isBlank;
}

function escapeUrl(s)
{
    var i = 0;
    var sNewUrl = "";

    for (i = 0; i < s.length; i++)
    {
        switch (s.charAt(i))
        {
            case '#':
                sNewUrl += "%23";
                break;

            case '?':
                sNewUrl += "%3F";
                break;

            case '&':
                sNewUrl += "%26";
                break;

            case '/':
                sNewUrl += "%2F";
                break;

            case '=':
                sNewUrl += "%3D";
                break;

            case '%':
                sNewUrl += "%25";
                break;

            case '"':
                sNewUrl += "%22";
                break;

            default:
                sNewUrl += s.charAt(i);
                break;
        }
    }
    return sNewUrl;
}

// Convert numbers to words
// copyright 25th July 2006, by Stephen Chapman http://javascript.about.com
// permission to use this Javascript on your web page is granted
// provided that all of the code (including this copyright notice) is
// used exactly as shown (you can change the numbering system if you wish)

// American Numbering System
var th =['', 'thousand', 'million', 'billion', 'trillion'];
// uncomment this line for English Number System
// var th = ['','thousand','million', 'milliard','billion'];

var dg =['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine'];
var tn =['ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
var tw =['twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

function ConvertNumberToWords(s)
{
    s = s.toString();
    s = s.replace(/[\, ]/g, '');
    s = String(parseFloat(s));
    var x = s.indexOf('.');
    if (x == -1) x = s.length; if (x > 15) return '';
    var n = s.split('');
    var str = '';
    var sk = 0;

    for (var i = 0; i < x; i++)
    {
        if ((x-i)%3==2)
        {
            if (n[i] == '1')
            {
                str += tn[Number(n[i+1])] + ' '; i++; sk = 1;
            }
            else if (n[i]!=0)
            {
                str += tw[n[i]-2] + ' '; sk = 1;
            }
        }
        else if (n[i]!=0)
        {
            str += dg[n[i]] +' '; if ((x-i)%3==0) str += 'hundred '; sk = 1;
        }
        if ((x-i)%3==1)
        {
            if (sk) str += th[(x-i-1)/3] + ' '; sk = 0;
        }
    }
    if (x != s.length)
    {
        var y = s.length;
        str += 'point ';
        for (var i = x+1; i<y; i++) str += dg[n[i]] +' ';
    }
    return str.replace(/\s+/g, ' ');
}


var MONTH_NAMES = new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
var DAY_NAMES = new Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
function LZ(x)
{
    return(x<0||x>9? "": "0")+x
}

// ------------------------------------------------------------------
// isDate ( date_string, format_string )
// Returns true if date string matches format of format string and
// is a valid date. Else returns false.
// It is recommended that you trim whitespace around the value before
// passing it to this function, as whitespace is NOT ignored!
// ------------------------------------------------------------------
function isDate(val, format)
{
    var date = getDateFromFormat(val, format);
    if (date==0)
    {
        return false;
    }
    return true;
}

// -------------------------------------------------------------------
// compareDates(date1,date1format,date2,date2format)
//   Compare two date strings to see which is greater.
//   Returns:
//   1 if date1 is greater than date2
//   0 if date2 is greater than date1 of if they are the same
//  -1 if either of the dates is in an invalid format
// -------------------------------------------------------------------
function compareDates(date1, dateformat1, date2, dateformat2)
{
    var d1 = getDateFromFormat(date1, dateformat1);
    var d2 = getDateFromFormat(date2, dateformat2);
    if (d1==0 || d2==0)
    {
        return -1;
    }
    else if (d1 > d2)
    {
        return 1;
    }
    return 0;
}

// ------------------------------------------------------------------
// formatDate (date_object, format)
// Returns a date in the output format specified.
// The format string uses the same abbreviations as in getDateFromFormat()
// ------------------------------------------------------------------
function formatDate(date, format)
{
    format = format+"";
    var result = "";
    var i_format = 0;
    var c = "";
    var token = "";
    var y = date.getYear()+"";
    var M = date.getMonth()+1;
    var d = date.getDate();
    var E = date.getDay();
    var H = date.getHours();
    var m = date.getMinutes();
    var s = date.getSeconds();
    var yyyy, yy, MMM, MM, dd, hh, h, mm, ss, ampm, HH, H, KK, K, kk, k;
    // Convert real date parts into formatted versions
    var value = new Object();
    if (y.length < 4)
    {
        y = ""+(y-0+1900);
    }
    value["y"] = ""+y;
    value["yyyy"] = y;
    value["yy"] = y.substring(2, 4);
    value["M"] = M;
    value["MM"] = LZ(M);
    value["MMM"] = MONTH_NAMES[M-1];
    value["NNN"] = MONTH_NAMES[M+11];
    value["d"] = d;
    value["dd"] = LZ(d);
    value["E"] = DAY_NAMES[E+7];
    value["EE"] = DAY_NAMES[E];
    value["H"] = H;
    value["HH"] = LZ(H);
    if (H==0)
    {
        value["h"] = 12;
    }
    else if (H>12)
    {
        value["h"] = H-12;
    }
    else
    {
        value["h"] = H;
    }
    value["hh"] = LZ(value["h"]);
    if (H>11)
    {
        value["K"] = H-12;
    }
    else
    {
        value["K"] = H;
    }
    value["k"] = H+1;
    value["KK"] = LZ(value["K"]);
    value["kk"] = LZ(value["k"]);
    if (H > 11)
    {
        value["a"] = "PM";
    }
    else
    {
        value["a"] = "AM";
    }
    value["m"] = m;
    value["mm"] = LZ(m);
    value["s"] = s;
    value["ss"] = LZ(s);
    while (i_format < format.length)
    {
        c = format.charAt(i_format);
        token = "";
        while ((format.charAt(i_format)==c) && (i_format < format.length))
        {
            token += format.charAt(i_format++);
        }
        if (value[token] != null)
        {
            result = result + value[token];
        }
        else
        {
            result = result + token;
        }
    }
    return result;
}

// ------------------------------------------------------------------
// Utility functions for parsing in getDateFromFormat()
// ------------------------------------------------------------------
function _isInteger(val)
{
    var digits = "1234567890";
    for (var i = 0; i < val.length; i++)
    {
        if (digits.indexOf(val.charAt(i))==-1)
        {
            return false;
        }
    }
    return true;
}
function _getInt(str, i, minlength, maxlength)
{
    for (var x = maxlength; x>=minlength; x--)
    {
        var token = str.substring(i, i+x);
        if (token.length < minlength)
        {
            return null;
        }
        if (_isInteger(token))
        {
            return token;
        }
    }
    return null;
}

// ------------------------------------------------------------------
// getDateFromFormat( date_string , format_string )
//
// This function takes a date string and a format string. It matches
// If the date string matches the format string, it returns the
// getTime() of the date. If it does not match, it returns 0.
// ------------------------------------------------------------------
function getDateFromFormat(val, format)
{
    val = val+"";
    format = format+"";
    var i_val = 0;
    var i_format = 0;
    var c = "";
    var token = "";
    var token2 = "";
    var x, y;
    var now = new Date();
    var year = now.getYear();
    var month = now.getMonth()+1;
    var date = 1;
    var hh = now.getHours();
    var mm = now.getMinutes();
    var ss = now.getSeconds();
    var ampm = "";

    while (i_format < format.length)
    {
        // Get next token from format string
        c = format.charAt(i_format);
        token = "";
        while ((format.charAt(i_format)==c) && (i_format < format.length))
        {
            token += format.charAt(i_format++);
        }
        // Extract contents of value based on format token
        if (token=="yyyy" || token=="yy" || token=="y")
        {
            if (token=="yyyy")
            {
                x = 4; y = 4;
            }
            if (token=="yy")
            {
                x = 2; y = 2;
            }
            if (token=="y")
            {
                x = 2; y = 4;
            }
            year = _getInt(val, i_val, x, y);
            if (year==null)
            {
                return 0;
            }
            i_val += year.length;
            if (year.length==2)
            {
                if (year > 70)
                {
                    year = 1900+(year-0);
                }
                else
                {
                    year = 2000+(year-0);
                }
            }
        }
        else if (token=="MMM"||token=="NNN")
        {
            month = 0;
            for (var i = 0; i<MONTH_NAMES.length; i++)
            {
                var month_name = MONTH_NAMES[i];
                if (val.substring(i_val, i_val+month_name.length).toLowerCase()==month_name.toLowerCase())
                {
                    if (token=="MMM"||(token=="NNN"&&i>11))
                    {
                        month = i+1;
                        if (month>12)
                        {
                            month -= 12;
                        }
                        i_val += month_name.length;
                        break;
                    }
                }
            }
            if ((month < 1)||(month>12))
            {
                return 0;
            }
        }
        else if (token=="EE"||token=="E")
        {
            for (var i = 0; i<DAY_NAMES.length; i++)
            {
                var day_name = DAY_NAMES[i];
                if (val.substring(i_val, i_val+day_name.length).toLowerCase()==day_name.toLowerCase())
                {
                    i_val += day_name.length;
                    break;
                }
            }
        }
        else if (token=="MM"||token=="M")
        {
            month = _getInt(val, i_val, token.length, 2);
            if (month==null||(month<1)||(month>12))
            {
                return 0;
            }
            i_val += month.length;
        }
        else if (token=="dd"||token=="d")
        {
            date = _getInt(val, i_val, token.length, 2);
            if (date==null||(date<1)||(date>31))
            {
                return 0;
            }
            i_val += date.length;
        }
        else if (token=="hh"||token=="h")
        {
            hh = _getInt(val, i_val, token.length, 2);
            if (hh==null||(hh<1)||(hh>12))
            {
                return 0;
            }
            i_val += hh.length;
        }
        else if (token=="HH"||token=="H")
        {
            hh = _getInt(val, i_val, token.length, 2);
            if (hh==null||(hh<0)||(hh>23))
            {
                return 0;
            }
            i_val += hh.length;
        }
        else if (token=="KK"||token=="K")
        {
            hh = _getInt(val, i_val, token.length, 2);
            if (hh==null||(hh<0)||(hh>11))
            {
                return 0;
            }
            i_val += hh.length;
        }
        else if (token=="kk"||token=="k")
        {
            hh = _getInt(val, i_val, token.length, 2);
            if (hh==null||(hh<1)||(hh>24))
            {
                return 0;
            }
            i_val += hh.length; hh--;
        }
        else if (token=="mm"||token=="m")
        {
            mm = _getInt(val, i_val, token.length, 2);
            if (mm==null||(mm<0)||(mm>59))
            {
                return 0;
            }
            i_val += mm.length;
        }
        else if (token=="ss"||token=="s")
        {
            ss = _getInt(val, i_val, token.length, 2);
            if (ss==null||(ss<0)||(ss>59))
            {
                return 0;
            }
            i_val += ss.length;
        }
        else if (token=="a")
        {
            if (val.substring(i_val, i_val+2).toLowerCase()=="am")
            {
                ampm = "AM";
            }
            else if (val.substring(i_val, i_val+2).toLowerCase()=="pm")
            {
                ampm = "PM";
            }
            else
            {
                return 0;
            }
            i_val += 2;
        }
        else
        {
            if (val.substring(i_val, i_val+token.length)!=token)
            {
                return 0;
            }
            else
            {
                i_val += token.length;
            }
        }
    }
    // If there are any trailing characters left in the value, it doesn't match
    if (i_val != val.length)
    {
        return 0;
    }
    // Is date valid for month?
    if (month==2)
    {
        // Check for leap year
        if (((year%4==0)&&(year%100 != 0)) || (year%400==0))
        {
            // leap year
            if (date > 29)
            {
                return 0;
            }
        }
        else
        {
            if (date > 28)
            {
                return 0;
            }
        }
    }
    if ((month==4)||(month==6)||(month==9)||(month==11))
    {
        if (date > 30)
        {
            return 0;
        }
    }
    // Correct hours value
    if (hh<12 && ampm=="PM")
    {
        hh = hh-0+12;
    }
    else if (hh>11 && ampm=="AM")
    {
        hh -= 12;
    }
    var newdate = new Date(year, month-1, date, hh, mm, ss);
    return newdate.getTime();
}

// ------------------------------------------------------------------
// parseDate( date_string [, prefer_euro_format] )
//
// This function takes a date string and tries to match it to a
// number of possible date formats to get the value. It will try to
// match against the following international formats, in this order:
// y-M-d   MMM d, y   MMM d,y   y-MMM-d   d-MMM-y  MMM d
// M/d/y   M-d-y      M.d.y     MMM-d     M/d      M-d
// d/M/y   d-M-y      d.M.y     d-MMM     d/M      d-M
// A second argument may be passed to instruct the method to search
// for formats like d/M/y (european format) before M/d/y (American).
// Returns a Date object or null if no patterns match.
// ------------------------------------------------------------------
function parseDate(val)
{
    var preferEuro = (arguments.length==2)? arguments[1]: false;
    generalFormats = new Array('y-M-d', 'MMM d, y', 'MMM d,y', 'y-MMM-d', 'd-MMM-y', 'MMM d');
    monthFirst = new Array('M/d/y', 'M-d-y', 'M.d.y', 'MMM-d', 'M/d', 'M-d');
    dateFirst = new Array('d/M/y', 'd-M-y', 'd.M.y', 'd-MMM', 'd/M', 'd-M');
    var checkList = new Array('generalFormats', preferEuro? 'dateFirst': 'monthFirst', preferEuro? 'monthFirst': 'dateFirst');
    var d = null;
    for (var i = 0; i<checkList.length; i++)
    {
        var l = window[checkList[i]];
        for (var j = 0; j<l.length; j++)
        {
            d = getDateFromFormat(val, l[j]);
            if (d!=0)
            {
                return new Date(d);
            }
        }
    }
    return null;
}

function textCounter(field, maxlimit)
{
    if (field.value.length > maxlimit) // if too long...trim it!
    {
        field.value = field.value.substring(0, maxlimit-1);
    }
}

function trim(str)
{
    return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

/**
 * http.js: utilities for scripted HTTP requests
 *
 * From the book JavaScript: The Definitive Guide, 5th Edition,
 * by David Flanagan. Copyright 2006 O'Reilly Media, Inc. (ISBN: 0596101996)
 */

// Make sure we haven't already been loaded
var HTTP;
if (HTTP && (typeof HTTP != "object" || HTTP.NAME))
    throw new Error("Namespace 'HTTP' already exists");

// Create our namespace, and specify some meta-information
HTTP =
{
};
HTTP.NAME = "HTTP"; // The name of this namespace
HTTP.VERSION = 1.0; // The version of this namespace

// This is a list of XMLHttpRequest creation factory functions to try
HTTP._factories =[
    function()
    {
        return new XMLHttpRequest();},
    function()
    {
        return new ActiveXObject("Msxml2.XMLHTTP");},
    function()
    {
        return new ActiveXObject("Microsoft.XMLHTTP");}
];

// When we find a factory that works, store it here
HTTP._factory = null;

/**
 * Create and return a new XMLHttpRequest object.
 *
 * The first time we're called, try the list of factory functions until
 * we find one that returns a nonnull value and does not throw an
 * exception.  Once we find a working factory, remember it for later use.
 */
HTTP.newRequest = function()
{
    if (HTTP._factory != null) return HTTP._factory();

    for (var i = 0; i < HTTP._factories.length; i++)
    {
        try
        {
            var factory = HTTP._factories[i];
            var request = factory();
            if (request != null)
            {
                HTTP._factory = factory;
                return request;
            }
        }
        catch(e)
        {
            continue;
        }
    }

    // If we get here, none of the factory candidates succeeded,
    // so throw an exception now and for all future calls.
    HTTP._factory = function()
    {
        throw new Error("XMLHttpRequest not supported");
    }
    HTTP._factory(); // Throw an error
}

/**
 * Use XMLHttpRequest to fetch the contents of the specified URL using
 * an HTTP GET request.  When the response arrives, pass it (as plain
 * text) to the specified callback function.
 *
 * This function does not block and has no return value.
 */
HTTP.getText = function(url, callback)
{
    var request = HTTP.newRequest();
    request.onreadystatechange = function()
    {
        if (request.readyState == 4 && request.status == 200)
            callback(request.responseText);
    }
    request.open("GET", url);
    request.send(null);
};

/**
 * Use XMLHttpRequest to fetch the contents of the specified URL using
 * an HTTP GET request.  When the response arrives, pass it (as a parsed
 * XML Document object) to the specified callback function.
 *
 * This function does not block and has no return value.
 */
HTTP.getXML = function(url, callback)
{
    var request = HTTP.newRequest();
    request.onreadystatechange = function()
    {
        if (request.readyState == 4 && request.status == 200)
            callback(request.responseXML);
    }
    request.open("GET", url);
    request.send(null);
};

/**
 * Use an HTTP HEAD request to obtain the headers for the specified URL.
 * When the headers arrive, parse them with HTTP.parseHeaders() and pass the
 * resulting object to the specified callback function. If the server returns
 * an error code, invoke the specified errorHandler function instead.  If no
 * error handler is specified, pass null to the callback function.
 */
HTTP.getHeaders = function(url, callback, errorHandler)
{
    var request = HTTP.newRequest();
    request.onreadystatechange = function()
    {
        if (request.readyState == 4)
        {
            if (request.status == 200)
            {
                callback(HTTP.parseHeaders(request));
            }
            else
            {
                if (errorHandler) errorHandler(request.status,
                        request.statusText);
                else callback(null);
            }
        }
    }
    request.open("HEAD", url);
    request.send(null);
};

/**
 * Parse the response headers from an XMLHttpRequest object and return
 * the header names and values as property names and values of a new object.
 */
HTTP.parseHeaders = function(request)
{
    var headerText = request.getAllResponseHeaders(); // Text from the server
    var headers =
    {
    }; // This will be our return value
    var ls = /^\s*/; // Leading space regular expression
    var ts = /\s*$/; // Trailing space regular expression

    // Break the headers into lines
    var lines = headerText.split("\n");
    // Loop through the lines
    for (var i = 0; i < lines.length; i++)
    {
        var line = lines[i];
        if (line.length == 0) continue; // Skip empty lines
        // Split each line at first colon, and trim whitespace away
        var pos = line.indexOf(':');
        var name = line.substring(0, pos).replace(ls, "").replace(ts, "");
        var value = line.substring(pos+1).replace(ls, "").replace(ts, "");
        // Store the header name/value pair in a JavaScript object
        headers[name] = value;
    }
    return headers;
};

/**
 * Send an HTTP POST request to the specified URL, using the names and values
 * of the properties of the values object as the body of the request.
 * Parse the server's response according to its content type and pass
 * the resulting value to the callback function.  If an HTTP error occurs,
 * call the specified errorHandler function, or pass null to the callback
 * if no error handler is specified.
 **/
HTTP.post = function(url, values, callback, errorHandler)
{
    var request = HTTP.newRequest();
    request.onreadystatechange = function()
    {
        if (request.readyState == 4)
        {
            if (request.status == 200)
            {
                callback(HTTP._getResponse(request));
            }
            else
            {
                if (errorHandler) errorHandler(request.status,
                        request.statusText);
                else callback(null);
            }
        }
    }

    request.open("POST", url);
    // This header tells the server how to interpret the body of the request
    request.setRequestHeader("Content-Type",
        "application/x-www-form-urlencoded");
    // Encode the properties of the values object and send them as
    // the body of the request.
    request.send(HTTP.encodeFormData(values));
};

/**
 * Encode the property name/value pairs of an object as if they were from
 * an HTML form, using application/x-www-form-urlencoded format
 */
HTTP.encodeFormData = function(data)
{
    var pairs =[];
    var regexp = /%20/g; // A regular expression to match an encoded space

    for (var name in data)
    {
        var value = data[name].toString();
        // Create a name/value pair, but encode name and value first
        // The global function encodeURIComponent does almost what we want,
        // but it encodes spaces as %20 instead of as "+". We have to
        // fix that with String.replace()
        var pair = encodeURIComponent(name).replace(regexp, "+") + '=' +
        encodeURIComponent(value).replace(regexp, "+");
        pairs.push(pair);
    }

    // Concatenate all the name/value pairs, separating them with &
    return pairs.join('&');
};

/**
 * Parse an HTTP response based on its Content-Type header
 * and return the parsed object
 */
HTTP._getResponse = function(request)
{
    // Check the content type returned by the server
    switch (request.getResponseHeader("Content-Type"))
    {
        case "text/xml":
            // If it is an XML document, use the parsed Document object
            return request.responseXML;

        case "text/json":
        case "application/json":
        case "text/javascript":
        case "application/javascript":
        case "application/x-javascript":
            // If the response is JavaScript code, or a JSON-encoded value,
            // call eval() on the text to "parse" it to a JavaScript value.
            // Note: only do this if the JavaScript code is from a trusted server!
            return eval(request.responseText);

        default:
            // Otherwise, treat the response as plain text and return as a string
            return request.responseText;
    }
};

/**
 * Send an HTTP GET request for the specified URL.  If a successful
 * response is received, it is converted to an object based on the
 * Content-Type header and passed to the specified callback function.
 * Additional arguments may be specified as properties of the options object.
 *
 * If an error response is received (e.g., a 404 Not Found error),
 * the status code and message are passed to the options.errorHandler
 * function.  If no error handler is specified, the callback
 * function is called instead with a null argument.
 *
 * If the options.parameters object is specified, its properties are
 * taken as the names and values of request parameters.  They are
 * converted to a URL-encoded string with HTTP.encodeFormData() and
 * are appended to the URL following a '?'.
 *
 * If an options.progressHandler function is specified, it is
 * called each time the readyState property is set to some value less
 * than 4.  Each call to the progress handler function is passed an
 * integer that specifies how many times it has been called.
 *
 * If an options.timeout value is specified, the XMLHttpRequest
 * is aborted if it has not completed before the specified number
 * of milliseconds have elapsed.  If the timeout elapses and an
 * options.timeoutHandler is specified, that function is called with
 * the requested URL as its argument.
 **/
HTTP.get = function(url, callback, options)
{
    var request = HTTP.newRequest();
    var n = 0;
    var timer;
    if (options.timeout)
        timer = setTimeout(function()
            {
                request.abort();
                if (options.timeoutHandler)
                    options.timeoutHandler(url);
            },
            options.timeout);

    request.onreadystatechange = function()
    {
        if (request.readyState == 4)
        {
            if (timer) clearTimeout(timer);
            if (request.status == 200)
            {
                callback(HTTP._getResponse(request));
            }
            else
            {
                if (options.errorHandler)
                    options.errorHandler(request.status,
                        request.statusText);
                else callback(null);
            }
        }
        else if (options.progressHandler)
        {
            options.progressHandler(++n);
        }
    }

    var target = url;
    if (options.parameters)
        target += "?" + HTTP.encodeFormData(options.parameters)
        request.open("GET", target);
    request.send(null);
};

HTTP.getTextWithScript = function(url, callback)
{
    // Create a new script element and add it to the document
    var script = document.createElement("script");
    document.body.appendChild(script);

    // Get a unique function name
    var funcname = "func" + HTTP.getTextWithScript.counter++;

    // Define a function with that name, using this function as a
    // convenient namespace.  The script generated on the server
    // invokes this function
    HTTP.getTextWithScript[funcname] = function(text)
    {
        // Pass the text to the callback function
        callback(text);

        // Clean up the script tag and the generated function
        document.body.removeChild(script);
        delete HTTP.getTextWithScript[funcname];
    }

    // Encode the URL we want to fetch and the name of the function
    // as arguments to the jsquoter.php server-side script.  Set the src
    // property of the script tag to fetch the URL
    script.src = "jsquoter.php" +
    "?url=" + encodeURIComponent(url) + "&func=" +
    encodeURIComponent("HTTP.getTextWithScript." + funcname);
}

// We use this to generate unique function callback names in case there
// is more than one request pending at a time.
HTTP.getTextWithScript.counter = 0;



/*********************

YUI API Methods


*/

var dtFld;
var pHandler;
var over_cal = false;
var focusFld;

function doYUIClose()
{
    parent.YUIWindow.hide();
    parent.revertParentButton();
    if (parent.focusFld && !parent.focusFld.disabled)
        parent.focusFld.focus();
    //YUIWindow = null;
}

function doCloseHandler()
{
    //alert(document.focusFld);
    //if (parent.focusFld && !parent.focusFld.disabled)
    //parent.focusFld.focus();
    var cc2 = parent.document.body.getElementById(getFocusFldHandler());
    if (cc2 != null && !cc2.disabled)
    {
        cc2.focus();
    }

}

function openYUICalendar(control)
{
    var cal1;
    cal1.render();
    cal1.show();


}

function closeNonModalWindow(ctrl)
{
    revertParentButton();
    ctrl.close();
}
function openNoModal(hdr, type, target, title, winWidth, panelWidth, panelHeight)
{

    //"width=480px,height=300px,resize=1,scrolling=1,center=1"
    var x, y, tmp, tmpW, w;
    panelWidth = winWidth - 20;

    x = Math.round(document.body.clientWidth/2);
    y = 5;
    tmp = document.body.clientHeight - 10;
    tmpW = parseInt(winWidth);

    var definedH = parseInt(panelHeight);

    if (definedH < tmp)
    {
        tmp = definedH;
    }

    if (parent != null)
        w = "width="+tmpW;
    else
        w = "width="+winWidth;
    w = w + ",height=" + tmp;

    var features = w+ ",resize=1,scrolling=1,center=1";
    pHandler = hdr;

    disableParentButton(true);
    var handle = dhtmlwindow.open(hdr, type, target, title, features);
    /*
    if (typeof jQuery != 'undefined')
        {
        $(".drag-handle").addClass("ui-widget-header");
        }*/
    handle.onclose = function()
    {
        //Run custom code when window is being closed (return false to cancel action):
        //closeNonModal();
        revertParentButton();

        return true;
    }

    return handle;
}

function openYUIModal(target, header, winWidth, panelWidth, panelHeight, f, p, c, parent, drag, ismodal, topOffset)
{

    if (!initYUI)
    {
        alert("Window is still loading. Please try again");
        return;
    }
    var x, y, tmp, tmpW;

    //winWidth = 950;
    panelWidth = winWidth - 20;

    if (parent != null)
    {

        /*
  y=Math.round(parent.body.clientHeight/2);
  */
        x = Math.round(parent.body.clientWidth/2);
        y = 5;

        tmp = parent.body.clientHeight - 10;
        tmpW = parseInt(winWidth) - 100;
    }
    else
    {
        /*
  y=Math.round(document.body.clientHeight/2);
  */
        x = Math.round(document.body.clientWidth/2);
        y = 5;
        tmp = document.body.clientHeight - 10;
        tmpW = parseInt(winWidth);
    }
    focusFld = f;
    pHandler = p;
    if (YUIWindow != null)
    {
        YUIWindow.hide();
    }

    var definedH = parseInt(panelHeight);
    var ddd = document.getElementById("myframe");

    if (definedH < tmp)
    {
        tmp = definedH;
    }
    doYUIinit();
    YUIWindow.render();
    if (c)
    {
        YUIWindow.cfg.setProperty("close", c);
    }

    if (drag!=null)
    {
        YUIWindow.cfg.setProperty("draggable", drag);
    }
    if (ismodal!=null)
    {
        YUIWindow.cfg.setProperty("modal", ismodal);
    }
    x = Math.round(x - (tmpW/2));
    // y = Math.round(y - (tmp/2));
    if (topOffset != null)
    {
        y = topOffset
    }
    YUIWindow.cfg.setProperty("x", x);
    YUIWindow.cfg.setProperty("y", y);

    if (parent != null)
        YUIWindow.cfg.setProperty("width", tmpW);
    else
        YUIWindow.cfg.setProperty("width", winWidth);
    YUIWindow.cfg.setProperty("height", tmp);
    YUIWindow.setHeader(header);
    //YUIWindow.setFooter(header);
    YUIHeader = header;
    showBusy(ddd);
    ddd.src = target;
    YUIWindow.show();

    if (typeof jQuery != 'undefined')
    {
        $(".yui-panel .hd").addClass("ui-widget-header");
    }

}


function handleJQModalClose(h)
{
}


function getJQModalHandle()
{
    return _JqHandle;
}




function openJQModal(target, title, h, w, resize, handle)
{
    var ddd = document.getElementById("JQiframe");
    // disableParentButton(true);

    _JqHandle = handle;
    $("#JQDialog").dialog(
        {
        autoOpen: false,
        width: w,
        title: title,
        height: h,
        resizable: resize,
        position:['center', 10],
        modal: true
        }
    )
    .css('padding', '0px')
    .css('overflow', 'hidden');
    ddd.src = target;

    $("#JQDialog").dialog(
        {
        close: function(event, ui)
            {
                var wHandle = $("#JQDialog").dialog("option", "title");
                $("#JQiframe").attr("src", "");
                $("#JQDialog").dialog("destroy");
                //RETURN FOCUS TO FIRST FIELD
                //QC 13388
                if ($('#noteTable'))
                {
                    _focusOnTable = true;
                }
                if (!_isIPad && !_focusOnTable)
                {
                    $(":input:visible:first").focus();
                }//      revertParentButton();
                handleJQModalClose(getJQModalHandle());
                return true;
            }
        }
    );
    $("#JQDialog").dialog('open');
}


function showBusy(content)
{
    var sHTML = '<html><body style="font:bold 20px arial;color:#933;text-align:center;">';
    sHTML += '<br /><br /><br /><IMG SRC="/images/lab/processing.gif">';
    sHTML += '</body></html>';
    var rr = document.getElementById('myframe').contentWindow.document;
    if (rr != null && rr.body)
    {
        rr.body.innerHTML = sHTML;
    }
}



function doYUIinit()
{
    //YAHOO.namespace('example.container');

    if (YUIWindow == null)
    {
        YUIWindow = new YAHOO.widget.Dialog("dlg",
            {
            constraintoviewport: false, modal: false, visible: false, underlay: "none", width: "750px", draggable: true, close: false}
        );
        /*
  YUIWindow.hideEvent.subscribe(function()
         {
          doCloseHandler();
         }, dlg, true);
  */
        //YAHOO.util.Event.addListener("container", "click", helloWorld);

        YUIWindow.cfg.setProperty('postmethod', 'none');
    }
}


function checkKey()
{
    if (YAHOO.util.Event.getCharCode(p_oEvent) === 27)
    {
        YAHOO.example.calendar.cal2.hide();
        dtFld.focus();
    }
}

function doFocus()
{
    examplecontainer.focus();
    over_cal = true;
}

function nextYear()
{
    YAHOO.example.calendar.cal2.nextYear();
}

function prevYear()
{
    YAHOO.example.calendar.cal2.previousYear();
}


function openCalendar(d)
{


    dtFld = d;
    valX = 0;
    valY = 0;
    locOf(d);


    YAHOO.namespace("example.calendar");

    YAHOO.example.calendar.cal2 = new YAHOO.widget.Calendar("cal2", "cal2Container",
        {
        close: true, iframe: true}
    );

    YAHOO.example.calendar.cal2.selectEvent.subscribe(mySelectHandler, YAHOO.example.calendar.cal2, true);
    YAHOO.example.calendar.cal2.renderEvent.subscribe(setupListeners, YAHOO.example.calendar.cal2, true);
    //YAHOO.example.calendar.cal2.changePageEvent.subscribe(doFocus, YAHOO.example.calendar.cal2, true);

    examplecontainer.style.pixelLeft = valX+(d.width*4);
    examplecontainer.style.pixelTop = valY+22;
    examplecontainer.style.zIndex = 1;


    YAHOO.util.Event.addListener("examplecontainer", "blur", closeCalendar);
    YAHOO.util.Event.addListener("cal2Container", "blur", closeCalendar);
    //YAHOO.util.Event.addListener("nextyear", "click", nextYear);
    //YAHOO.util.Event.addListener("prevyear", "click", prevYear);

    //YAHOO.util.Event.addListener("examplecontainer", "keydown", checkKey);

    YAHOO.example.calendar.cal2.render();
    YAHOO.example.calendar.cal2.show();
    examplecontainer.focus();
}

function setupListeners()
{
    YAHOO.util.Event.addListener('cal2Container', 'mouseover', overCal);
    YAHOO.util.Event.addListener('cal2Container', 'mouseout', outCal);
    YAHOO.util.Event.addListener('cal2Container', 'hide', hideFired);

}

function hideFired()
{
    alert("Hide");
}


function overCal()
{
    over_cal = true;
}

function outCal()
{
    over_cal = false;
}


function closeCalendar()
{
    if (!over_cal)
    {
        YAHOO.example.calendar.cal2.hide();
    }
}

function mySelectHandler(type, args, obj)
{
    var selected = args[0];
    var selDate = YAHOO.example.calendar.cal2.toDate(selected[0]);

    var aDate,
    nMonth,
    nDay,
    nYear;

    if (args)
    {

        aDate = args[0][0];

        nMonth = aDate[1];
        nDay = aDate[2];
        nYear = aDate[0];

        dtFld.value = nMonth + "/" + nDay + "/" + nYear;
    }


    //alert(selDate.toString());
    //dfFld.text= dateToLocaleString(selDate, YAHOO.example.calendar.cal);
    YAHOO.example.calendar.cal2.hide();
}

function closeNonModal()
{
    revertParentButton();
    parent.handleNonModalClose(getPanelHandler());

}

function closeJQModal(title)
{
    if ($('#JQDialog').length != 0 && $("#JQDialog").dialog("isOpen"))
    {
        //        alert ($("#JQDialog").dialog('option','title'));
        if (!title || title == $("#JQDialog").dialog('option', 'title'))
        {
            $("#JQDialog").dialog('close');
        }
    }
    //revertParentButton();
    //handleJQModalClose(getJQModalHandle());
}


function closeWindow()
{
    if (parent.YAHOO != null)
    {
        doYUIClose();
    }
    else
    {
        window.close();
    }
}

function getPanelHandler()
{
    return getJQModalHandle();
    //return parent.pHandler;
}

function getFocusFldHandler()
{
    return parent.focusFld;
}


function showProcessing()
{
    YAHOO.namespace("example.container");



    // Initialize the temporary Panel to display while waiting for external content to load

    YAHOO.example.container.wait =
    new YAHOO.widget.Panel("wait",
        {
        width: "200px",
        fixedcenter: true,
        close: false,
        draggable: false,
        zindex: 4,
        underlay: "shadow",
        modal: true,
        visible: false
        }

    );

    //var img = "<IMG SRC=" +  "\"" + "/images/lab/search.gif" + "\"" + ">");


    YAHOO.example.container.wait.setHeader("<img vspace=\"5\" src=\"/images/lab/loading.gif\"/>&nbsp;&nbsp;Loading, please wait...");

    YAHOO.example.container.wait.render(document.body);

    // Show the Panel
    YAHOO.example.container.wait.show();
    YAHOO.util.Event.addListener(document.body, "load", showProcessing);



}

function hideProcessing()
{

    YAHOO.example.container.wait.hide();

}


/****

Methods to support highligt row as user mouse over on a HTML table

*/

var firstRow, secondRow, baseLocation, rowSelected = false, el, upButtonID,
downButtonID, bTableID, origRow;

//action handler for mouseover on a row
function onMouseOverHandler()
{
    var curRow = window.event.srcElement.parentElement;
    curRow.style.cursor = 'Hand';
    curRow.style.backgroundColor = '#aefdcb';
    curRow.style.color = 'black';
}

//action handler for mouseout on a row
function onMouseOutHandler()
{
    var curRow = window.event.srcElement.parentElement;
    curRow.style.cursor = '';
    curRow.style.backgroundColor = '';
    curRow.style.color = '';
}

//mark a row on a click of the mouse
function onRowClickHandler() //tblname)
{
    firstRow = window.event.srcElement.parentElement;
    for (i = 0; i<baseLocation.rows.length; i++)
        if (i == parseInt(firstRow.getAttribute("id")))
    {
        if (firstRow.cells(0).childNodes[0].type == 'checkbox')
            //firstRow.cells(0).childNodes[0].checked = true;
            firstRow.style.backgroundColor = 'Wheat';
        firstRow.style.color = 'Black';
        firstRow.onmouseout = '';
        firstRow.onmouseover = '';
        rowSelected = true;
    }
    else
    {
        if (baseLocation.rows(i).cells(0).childNodes[0].type == 'checkbox')
            //baseLocation.rows(i).cells(0).childNodes[0].checked = false;
            baseLocation.rows(i).style.backgroundColor = '';
        baseLocation.rows(i).style.color = '';
        baseLocation.rows(i).onmouseout = onMouseOutHandler;
        baseLocation.rows(i).onmouseover = onMouseOverHandler;
    }
}


//action handler when the the movedown button is clicked
function onMoveDownClick()
{
    if (rowSelected)
    {
        handleMoveDown();
        el = baseLocation.all.tags("INPUT"), i = 0, arr =[];
        for (; i < el.length; i++)
            if (el[i].type == "checkbox")
            arr.push(el[i], el[i].checked);
        if (parseInt(firstRow.getAttribute("id"), 10) < (baseLocation.rows.length - 1))
        {
            moveDown();
            if ((firstRow.cells(0).firstChild != null))
            {
                firstRow.cells(0).firstChild.focus();
            }
        }
    }
    else
        alert("Select a Row");
}

//action handler when the the moveup button is clicked
function onMoveUpClick()
{
    if (rowSelected)
    {
        handleMoveUp();
        el = baseLocation.all.tags("INPUT"), i = 0, arr =[];
        for (; i < el.length; i++)
            if (el[i].type == "checkbox")
            arr.push(el[i], el[i].checked);
        if (parseInt(firstRow.getAttribute("id"), 10) > 0)
        {
            moveUp();
            if ((firstRow.cells(0).firstChild != null))
            {
                firstRow.cells(0).firstChild.focus();
            }
        }
    }
    else
        alert("Select a Row");
}

//action handler for mouseover on a row
function onMouseOverHandler()
{
    var curRow = window.event.srcElement.parentElement;
    curRow.style.cursor = 'Hand';
    curRow.style.backgroundColor = '#aefdcb';
    curRow.style.color = 'black';
}

//action handler for keypress on the page
function onKeyDownHandler()
{
    var key = window.event.keyCode;
    if (key == 27)
    {
        rowSelected = false;
        setTable(bTableID, upButtonID, downButtonID);
    }
    else if (key == 38)
    {
        if (rowSelected)
        {
            var prevID = parseInt(firstRow.getAttribute("id"), 10);
            if (prevID > 0)
            {
                firstRow = document.getElementById(prevID - 1);
                for (i = 0; i<baseLocation.rows.length; i++)
                    if (i == parseInt(firstRow.getAttribute("id")))
                {
                    firstRow.style.backgroundColor = 'Wheat';
                    firstRow.style.color = 'Black';
                    firstRow.onmouseout = '';
                    firstRow.onmouseover = '';
                    rowSelected = true;
                }
                else
                {
                    baseLocation.rows(i).style.backgroundColor = '';
                    baseLocation.rows(i).style.color = '';
                    baseLocation.rows(i).onmouseout = onMouseOutHandler;
                    baseLocation.rows(i).onmouseover = onMouseOverHandler;
                }
            }
        }
        else
            alert("Select a Row");
    }
    else if (key == 40)
    {
        if (rowSelected)
        {
            var prevID = parseInt(firstRow.getAttribute("id"), 10);
            if (prevID < (baseLocation.rows.length - 1))
            {
                firstRow = document.getElementById(prevID + 1);
                for (i = 0; i<baseLocation.rows.length; i++)
                    if (i == parseInt(firstRow.getAttribute("id")))
                {
                    firstRow.style.backgroundColor = 'Wheat';
                    firstRow.style.color = 'Black';
                    firstRow.onmouseout = '';
                    firstRow.onmouseover = '';
                    rowSelected = true;
                }
                else
                {
                    baseLocation.rows(i).style.backgroundColor = '';
                    baseLocation.rows(i).style.color = '';
                    baseLocation.rows(i).onmouseout = onMouseOutHandler;
                    baseLocation.rows(i).onmouseover = onMouseOverHandler;
                }
            }
        }
        else
            alert("Select a Row");
    }
}




//initialize the table; this method that needs an explicit call during the onload of the body
function setTable(bTable, mUpButton, mDownButton)
{
    var iAnalyteIndex = 0;
    upButtonID = mUpButton;
    downButtonID = mDownButton;
    bTableID = bTable;
    baseLocation = document.getElementById(bTableID);
    for (i = 0; i<baseLocation.cells.length; i++)
        baseLocation.cells(i).unselectable = "On";
    for (i = 0; i<baseLocation.rows.length; i++)
    {
        baseLocation.rows(i).setAttribute("id", i);
        baseLocation.rows(i).onclick = onRowClickHandler;
        if (!rowSelected)
        {
            baseLocation.rows(i).style.backgroundColor = '';
            baseLocation.rows(i).style.color = '';
            baseLocation.rows(i).onmouseover = onMouseOverHandler;
            baseLocation.rows(i).onmouseout = onMouseOutHandler;
        }
    }

    document.getElementById(mUpButton).onclick = onMoveUpClick;
    document.getElementById(mDownButton).onclick = onMoveDownClick;
    document.onkeydown = onKeyDownHandler;
}

function moveDown()
{
    secondRow = document.getElementById(parseInt(firstRow.getAttribute("id"), 10) + 1);
    if (secondRow.cells(0).getAttribute("id") == firstRow.cells(0).getAttribute("id"))
    {
        baseLocation.moveRow(firstRow.getAttribute("id"), secondRow.getAttribute("id"));
        while (arr.length > 0)
            arr.shift().checked = arr.shift();
        firstRow.onmouseout = '';
        firstRow.onmouseover = '';
        setTable(bTableID, upButtonID, downButtonID);
    }
}

function moveUp()
{
    secondRow = document.getElementById(parseInt(firstRow.getAttribute("id"), 10) - 1);
    if (secondRow.cells(0).getAttribute("id") == firstRow.cells(0).getAttribute("id"))
    {
        baseLocation.moveRow(firstRow.getAttribute("id"), secondRow.getAttribute("id"));
        while (arr.length > 0)
            arr.shift().checked = arr.shift();
        firstRow.onmouseout = '';
        firstRow.onmouseover = '';
        setTable(bTableID, upButtonID, downButtonID);
    }
}

//call backs
function handleMoveUp()
{
}
function handleMoveDown()
{
}


/****
End Of Methods to support highlight row as user mouse over on a HTML table

*/



/**
YUI MOdal Wizrd methods

*/
function doWizNextPage(t)
{
    var ddd = document.getElementById("myframe");
    if (ddd != null)
    {
        ddd.src = t;
    }
}

function doWizNextPageNoModal(win, url, h)
{

    var ddd = document.getElementById("JQiframe");
    ddd.src = url;
    //win.load('iframe', url, h);
}


/*
 var wizardPages="";
 var currwizPage="";
 var currWizPageIndex=0;




 function doWizPrev()
 {
  var ddd = document.getElementById("myframe");
  //get page name
  var tmp = wizardPages.split("|");
  if (currWizPageIndex > 0)
   {
   currWizPageIndex--;
   ddd.src=tmp[currWizPageIndex];
   }
  else
   {
   ddd.src=tmp[0];
   }

 }

 function doWizNext()
 {
 var ddd = document.getElementById("myframe");
 //get page name
 var tmp = wizardPages.split("~");
  if (currWizPageIndex < tmp.length)
   {
   currWizPageIndex++;
   //get page name
   ddd.src=tmp[currWizPageIndex];
   }
 }



 function openYUIWizardModal(targetPages, header, winWidth, panelWidth, panelHeight, f, p, c, parent)
 {

 var x, y, tmp, tmpW;

 winWidth = 1050;
 panelWidth = winWidth - 20;

  if (parent != null) {

   x=Math.round(parent.body.clientWidth/2);
   y=Math.round(parent.body.clientHeight/2);
   tmp = parent.body.clientHeight - 10;
   tmpW = parseInt(winWidth) - 100;
 }
  else
 {
   x=Math.round(document.body.clientWidth/2);
   y=Math.round(document.body.clientHeight/2);
   tmp = document.body.clientHeight - 10;
   tmpW = parseInt(winWidth);
  }
  focusFld = f;
 pHandler = p;
 if (YUIWindow != null)
  {
  YUIWindow.hide();
 }

 var definedH = parseInt(panelHeight);
 var ddd = document.getElementById("myframe");

 if (definedH < tmp)
  {
  tmp = definedH;
  }
 doYUIinit();
 YUIWindow.render();
 if (c)
  {
  YUIWindow.cfg.setProperty("close",c);
  }

 x = Math.round(x - (tmpW/2));
 y = Math.round(y - (tmp/2));
 YUIWindow.cfg.setProperty("x", x);
 YUIWindow.cfg.setProperty("y", y);

 if (parent != null)
  YUIWindow.cfg.setProperty("width",tmpW);
 else
  YUIWindow.cfg.setProperty("width",winWidth);
 YUIWindow.cfg.setProperty("height", tmp);
 YUIWindow.setHeader(header);
 //YUIWindow.setFooter(header);
 YUIHeader = header;
 showBusy(ddd);
 ddd.src= target[0];
 YUIWindow.show();
 //var ddd2 = document.getElementById("btnPrev");
 //alert(ddd2.name);

 }

*/


function setupBtns (style)
{
    $(style)
    .addClass("ui-state-default")
    .hover(
        function()
        {
            $(this).addClass("ui-state-hover");
        },
        function()
        {
            $(this).removeClass("ui-state-hover");
        }
    )
    .mousedown(function()
        {
            $(this).addClass("ui-state-active");
        }
    )
    .mouseout(function()
        {
            $(this).removeClass("ui-state-active");
        }
    )
    .mouseup(function()
        {
            $(this).removeClass("ui-state-active");
        }
    );
}


if (typeof jQuery != 'undefined')
{
    $(function()
        {
            setupBtns ('.btn');
            setupBtns ('.tbrBtn');
            setupBtns ('.tbrbtn');

            $('.stepnum').addClass('ui-widget-header');
            $('.colTitle').addClass('ui-widget-header');

            if (!$.browser.msie)
            {
                setupAccessKeys();
            }

            var p = parent;
            while (p && p != p.parent)
            {
                if (p.setJQModalSize && p.setJQModalSize ($(document).height(), $(document).width()))
                {
                    break;
                }
                else
                    p = p.parent;
            }
        }
    );
}

function setJQModalSize (height, width)
{
    if ($('#JQDialog').length > 0 && $('#JQDialog').dialog('isOpen') == true)
    {
        height += 200;
        if (height < $('#JQDialog').dialog('height'))
        {
            height = $('#JQDialog').dialog('height');
        }
        if (!_isIPad && height > $(window).height())
        {
            height = $(window).height();
        }

        $("#JQDialog").dialog("option", "height", height);
        //                      .dialog( "option", "width", width );
        return true;
    }
    return false;
}


function redoTabs ()
{
    $(':input:visible').each(function(i, e)
        {
            $(e).attr('tabindex', i)}
    );
}


var _isAlt = false;
var _accessKeys =[];

function setupAccessKeys ()
{
    var i = 0;
    $('button').each(function ()
        {
            _accessKeys[$(this).attr('accesskey')] = $(this);
        }
    );

    $(document)
    .keyup(function (e)
        {
            if(e.altKey) _isAlt = false;
        }
    )
    .keydown(function (e)
        {
            if (e.altKey)
            {
                _isAlt = true;
            }
            var btn = _accessKeys[String.fromCharCode(e.which)]
            if (_isAlt && btn)
            {
                btn.click();
                e.preventDefault();
                e.stopPropagation();

                return false;
            }
        }
    );
}


function revertParentButton()
{
    if (_buttonArray != null)
    {
        for (var i = 0; i<_buttonArray.length; i++)
        {
            _buttonArray[i].disabled = false;
        }
    }
}

function disableParentButton(f)
{
    _buttonArray = new Array();
    if (document.forms[0])
    {
        var all = document.forms[0].elements;
        var j = 0;
        for (var i = 0; i<all.length; i++)
        {
            if (all[i].type && all[i].type.toLowerCase() == 'button' && !all[i].disabled)
            {
                _buttonArray[j++] = all[i];
                all[i].disabled = f;
            }
        }
        doCustomButtonHandler();
    }
}

function doCustomButtonHandler()
{
}

function encodeValues(ctrl)
{
    ctrl.value = encodeHTML(ctrl.value);
}


function encodeHTML (sData)
{
    if (sData == null)
    {
        return "";
    }
    var sbNew = "";
    for (var i = 0; i < sData.length; i++)
    {
        var ch = sData.charAt(i);
        switch (ch)
        {
            case '~':
                sbNew = sbNew +"&#126;";
                break;
            case '^':
                sbNew = sbNew +"&#94;";
                break;
            case '\'':
    sbNew = sbNew + "&#39;";
    break;
   case '\"': // or &#34;
    sbNew = sbNew + "&quot;";
    break;
   case '%':
    sbNew = sbNew +"&#37;";
    break;
   case '|':
    sbNew = sbNew +"&#166;";
    break;

    /*
    case '!':
                 sbNew = sbNew +"&#33;";
                 break;
    case '#':
                 sbNew = sbNew +"&#35;";
                 break;
         case '$':
                 sbNew = sbNew +"&#36;";
                 break;
             case '&': //  or &#38;
                 sbNew = sbNew +"&amp;";
                 break;
             case '(':
                 sbNew = sbNew +"&#40;";
                 break;
             case ')':
                 sbNew = sbNew +"&#41;";
                 break;
             case '*':
                 sbNew = sbNew +"&#42;";
                 break;
    case '+':
                 sbNew = sbNew +"&#43;";
                 break;
             case '>': // or &#60;
                 sbNew = sbNew +"&gt;";
                 break;
             case '=':
                 sbNew = sbNew +"&#61;";
                 break;
             case '<': // or &#62;
                 sbNew = sbNew +"&lt;";
                 break;
             case '@':
                 sbNew = sbNew +"&#64;";
                 break;
             case '\\':
                 sbNew = sbNew +"&#92;";
                 break;
             case '_':
                 sbNew = sbNew +"&#95;";
                 break;
    */
   default:
    sbNew = sbNew + ch;
  }
 }
 return sbNew;
}


function processSpecialTestCodes(codes)
{
 var j = 0;
 var t = new Array();
 var notFound = "";
 if (codes == null)
  return notFound;

 var sData = "";

 for (var i = 0; i <= codes.length; i++)
 {
  //keep reading till end of quote
  if (codes.charAt(i) == "'")
  {
   i++;
   while (codes.charAt(i) != "'" && (i < codes.length))
   {
    sData = sData + codes.charAt(i);
    i++;
   }
   //found end of quote
   if (codes.charAt(i) == "'")
   {
    t[j] = sData;
    sData = "";
    j++;
    i++;
   }
  }
  else if (codes.charAt(i) != " " || codes.charAt(i) != ",")
  {
   sData = sData + codes.charAt(i);
  }

  if (codes.charAt(i+1) == " " || codes.charAt(i+1) == ",")
  {
   t[j] = sData;
   sData = "";
   j++;
   i++;

  }


 } // end of for

 if (sData != "");
 t[j] = sData;
 //    alert("array:"+t.toString());
 var arr = new Array(t.length);
 for (var j = 0; j<t.length; j++)
 {
  arr[j] = t[j];
 }
 for (var j = 0; j<arr.length; j++)
 {
  var code = arr[j];
  code = ltrim(code);
  if (code != "")
  {
   notFound = notFound + code + "^"
  }
 }
 return notFound;
}

function ltrim(str, chars) {
 chars = chars || "\\s";
 return str.replace(new RegExp("^[" + chars + "]+", "g"), "");
}


function HTMLDecode(c)
{

 var result = "";
 result = Encoder.htmlDecode(c);
 // alert(result);
 return result;
}

function HTMLEncode(c)
{
 // set the type of encoding to numerical entities e.g &#38; instead of &amp;
 Encoder.EncodeType = "numerical";

 // or to set it to encode to html entities e.g &amp; instead of &#38;
 //Encoder.EncodeType = "entity";

 var result = "";
 result = Encoder.htmlEncode(c);
 //alert(result);
 return result;
}


function goToAnchor(anchorName)
{
 window.location = "#"+anchorName;
}


function hasScriptX()
{
 return document.getElementById('factory').object;
 /*   For some reason, this code DOES NOT WORK.  The one line above does work, so we'll use it.
            var factory = $('#factory').object;
            if (!factory)
                            {
                            return false;
                            }
            else
                            {
                            return factory;
                            }
    */
}