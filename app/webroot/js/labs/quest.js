var s_nMainEdge= 208;
var s_nPageCount= 0;
var s_nBottomEdge= 912;
var s_nPageArea= 1008;
var s_nReportFixed= 7;
var s_bTextOnly= 1;

var real_page_count = 0;

function addContent(html)
{
		var target_control = $('#report_all_content_'+real_page_count);
		if ( window.paginate && parseInt(real_page_count, 10) != window.currentPage) {
			return false;
		}
		if(target_control.length == 0)
		{
			var new_div = '';
			new_div += '<div style="clear: both;"></div>';
			new_div += '<div class="page_item" id="report_all_content_'+real_page_count+'" style="position: relative; overflow: visible; height: 1030px;"></div>';
	
			var div_container = $('<div class="div_container"></div>');
			div_container.html(new_div);
			$("body").append(div_container);
			$("body").append('<div style="clear: both;"></div><div class="page-break"></div>');
		}
		
		var current_html = $('#report_all_content_'+real_page_count).html();
		current_html += html;
		$('#report_all_content_'+real_page_count).html(current_html);
	
}

function istrcomp(a_sFirst, a_sSecond)
{
    return a_sFirst.toUpperCase() == a_sSecond.toUpperCase();
}

function trim(a_sText)
{
    var z_sAnswer= a_sText;
    for ( var t= 0; t < z_sAnswer.length; t++ )
    {
        if ( z_sAnswer.charAt(t) != ' ' )
        {
            z_sAnswer= z_sAnswer.substr(t);
            break;
        }
    }
    for ( var s= z_sAnswer.length; s > 0; s++ )
    {
        if ( z_sAnswer.charAt(s - 1) != ' ' )
        {
            z_sAnswer= z_sAnswer.substr(0, s);
            break;
        }
    }
    return z_sAnswer;
}

// function
function escape_text(a_sText)
{
    a_sText= a_sText.replace(/\&/g, '&amp;');
    a_sText= a_sText.replace(/\</g, '&lt;');
    a_sText= a_sText.replace(/\>/g, '&gt;');
    return a_sText;
}//end function

function write_header_item(a_sText, a_sFont, a_nLeft, a_nTop, a_nWidth)
{
    if ( a_sText.length > a_nWidth )
    {
        addContent('<pre style="font: ' + a_sFont + '; position: absolute; margin: 0; padding: 0;  left: ' + a_nLeft + 'px; top: ' + a_nTop + 'px">' + escape_text(a_sText.substr(0, a_nWidth)) + '<\/pre>');
    }
    else
    {	
        addContent('<pre style="font: ' + a_sFont + '; position: absolute; margin: 0; padding: 0;  left: ' + a_nLeft + 'px; top: ' + a_nTop + 'px">' + escape_text(a_sText) + '<\/pre>');
    }
}//end function

function write_header_item_color(a_sText, a_sFont, a_nLeft, a_nTop, a_nWidth, a_sColor)
{
    if ( a_sText.length > a_nWidth )
    {
        addContent('<pre style="font: ' + a_sFont + '; color: ' + a_sColor + '; position: absolute; margin: 0; padding: 0;  left: ' + a_nLeft + 'px; top: ' + a_nTop + 'px">' + escape_text(a_sText.substr(0, a_nWidth)) + '<\/pre>');
    }
    else
    {
        addContent('<pre style="font: ' + a_sFont + '; color: ' + a_sColor + '; position: absolute; margin: 0; padding: 0;  left: ' + a_nLeft + 'px; top: ' + a_nTop + 'px">' + escape_text(a_sText) + '<\/pre>');
    }
}//end function

function name_display(a_sAltName, a_sLastName, a_sFirstName, a_sMiddleName, a_sSuffix)
{
    if (a_sLastName.length > 0 )
    {
        var z_sAnswer= a_sLastName;
        if (a_sSuffix.length > 0)
        {
            z_sAnswer += ' ' + a_sSuffix;
        }
        if (a_sFirstName.length > 0)
        {
            return z_sAnswer += ', ' + a_sFirstName + ' ' + a_sMiddleName;
        }
        else
        {
            return z_sAnswer;
        }//end if-else
    }
    else
    {
        return a_sAltName;
    }//end if-else
}//end function

function client_name_display(a_sAltName, a_sLastName, a_sFirstName, a_sMiddleName, a_sSuffix)
{
    if (a_sLastName.length > 0 )
    {
        var z_sAnswer= a_sFirstName;
        z_sAnswer += a_sMiddleName.length > 0? ' ' + a_sMiddleName: '';
        z_sAnswer += ' ' + a_sLastName + ' ' + a_sSuffix;
        return z_sAnswer;
    }
    else
    {
        return a_sAltName;
    }//end if-else
}//end function

function ClinicianDate(a_sDate)
{
    this.m_date= new Date();
    var z_month= a_sDate.indexOf('\/', 0);
    var z_day= a_sDate.indexOf('\/', z_month + 1);
    if ( z_month == -1 || z_day == -1 )
    {
        this.m_bValid= false;
    }
    else
    {
        this.m_bValid= true;
        this.m_date.setUTCFullYear(parseInt(a_sDate.substr(z_day + 1, 4)), parseInt(a_sDate.substr(0, z_month)) - 1, parseInt(a_sDate.substr(z_month + 1, z_day - z_month)));
    }	
}//end function	
	
function calculate_age()
{
    var z_sPersonAge= new String(parseInt(s_report.orderresult.person_age));
    if ( z_sPersonAge.length == 0 || z_sPersonAge == 'NaN' )
    {
        if ( s_report.orderresult.collection_datetime.length > 0 && s_report.person.birth_date.length > 0)
        {
            var z_colldate= new ClinicianDate(s_report.orderresult.collection_datetime);
            var z_birthdate= new ClinicianDate(s_report.person.birth_date);
            if ( z_colldate.m_bValid && z_birthdate.m_bValid )
            {
                var z_difference= new Date();
                z_difference.setTime((z_colldate.m_date.getTime())- z_birthdate.m_date.getTime());
                if ( z_difference.getUTCFullYear() - 1970 > 0)
                {
                    s_report.orderresult.person_age= z_difference.getUTCFullYear() - 1970;
                    s_report.orderresult.person_age_type= 'Y';
                }
                else
                {
                    if (z_difference.getMonth() > 0 )
                    {
                        s_report.orderresult.person_age= z_difference.getUTCMonth();
                        s_report.orderresult.person_age_type= 'M';
                    }
                    else
                    {
                        s_report.orderresult.person_age= z_difference.getUTCDate();
                        s_report.orderresult.person_age_type= 'D';
                    }//end if-else
                }//end if-else
            }//end if	
        }//end if
        z_sPersonAge= new String(s_report.orderresult.person_age);
    }
    if ( s_report.orderresult.person_age_type.charAt(0) != 'Y' )
    {
        z_sPersonAge += s_report.orderresult.person_age_type.charAt(0);
    }
    return z_sPersonAge;	
}	

function display_type()
{
    if ( s_report.clinicalreport.report_type == 'ERAD' )
    {
        return 'ER DIAGNOSTIC IMAGING';
    }
    if ( s_report.clinicalreport.report_type == 'IRAD' )
    {
        return 'INPATIENT DIAGNOSTIC IMAGING';
    }
    if ( s_report.clinicalreport.report_type == 'ORAD' )
    {
        return 'OUTPATIENT DIAGNOSTIC IMAGING';
    }
    if ( s_report.clinicalreport.report_type == 'RAD' )
    {
        return 'DIAGNOSTIC IMAGING';
    }
    if ( s_report.clinicalreport.report_type == 'ERLAB' )
    {
        return 'ER LABORATORY REPORT';
    }
    if ( s_report.clinicalreport.report_type == 'ILAB' )
    {
        return 'INPATIENT LABORATORY REPORT';
    }
    if ( s_report.clinicalreport.report_type == 'OLAB' )
    {
        return 'OUTPATIENT LABORATORY REPORT';
    }
    return 'LABORATORY REPORT';
}	

function page_header(a_n)
{
	if ( s_sLabLogo.length > 0 )
	{
		addContent('<IMG src=' + s_sLabLogo + ' ALT=' + s_sLabLogoText + ' style="position: absolute; margin: 0; padding: 0; left: 406px; top: ' + (a_n + 32) + 'px">');
	}
    var z_sName= client_name_display(s_report.orderresult.receiving_cg_id, s_report.caregiver.last_name, s_report.caregiver.first_name, s_report.caregiver.middle_name, s_report.caregiver.suffix);
    write_header_item(display_type(), 'bold 9pt Times New Roman', 406, a_n + 10, 30);
    write_header_item(s_report.lab.lab_name, 'bold 9pt Times New Roman', 406, a_n + 75, 60);
    if ( s_report.lab.address_1.length > 0 )
    {
        write_header_item(s_report.lab.address_1 + ' ' + s_report.lab.address_2 + ', ' + s_report.lab.city + ' ' + s_report.lab.state + ' ' + s_report.lab.zip, 'bold 9pt Times New Roman', 406, a_n + 87, 60);
    }
    if ( s_report.lab.phone_number.length == 7 )
    {
        write_header_item('(' + s_report.lab.phone_area_code + ') ' + s_report.lab.phone_number.substr(0,3) + '-' + s_report.lab.phone_number.substr(3,4), 'bold 9pt Times New Roman', 406, a_n + 99, 15);
    }
    if ( s_report.lab.director_name_1.length > 0 )
    {
        write_header_item('Director: ' + s_report.lab.director_name_1, 'bold 9pt Times New Roman', 521, a_n + 99, 48);
    }
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 0; top: ' + (a_n + 10) + 'px; width: 370; height: 70"><\/div>');
    write_header_item(z_sName, 'bold 9pt Times New Roman', 10, a_n + 13, 48);
    write_header_item(s_report.organization.organization_name, 'bold 9pt Times New Roman', 10, a_n + 29, 48);
    write_header_item(s_report.organization.mailing_address_1 + ' ' + s_report.organization.mailing_address_2, 'bold 9pt Times New Roman', 10, a_n + 45, 48);
    write_header_item(s_report.organization.mailing_city + ' ' + s_report.organization.mailing_state + ' ' + s_report.organization.mailing_zip, 'bold 9pt Times New Roman', 10, a_n + 61, 48);
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 0; top: ' + (a_n + 85) + 'px; width: 370; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 264; top: ' + (a_n + 85) + 'px; width: 96; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 317; top: ' + (a_n + 85) + 'px; width: 53; height: 34"><\/div>');
    write_header_item('Patient Name', '7pt Arial', 5, a_n + 87, 32);
    write_header_item('Sex', '7pt Arial', 270, a_n + 87, 4);
    write_header_item('Age', '7pt Arial', 323, a_n + 87, 4);
    var z_sPatientName= name_display('UNKNOWN', s_report.person.last_name, s_report.person.first_name, s_report.person.middle_name,s_report.person.suffix);
    write_header_item(z_sPatientName, 'bold 9pt Arial', 6, a_n + 99, 35);
    write_header_item(s_report.person.sex, 'bold 9pt Arial', 277, a_n + 99, 4);
    var z_sPersonAge= calculate_age();
    write_header_item(z_sPersonAge, 'bold 9pt Arial', 328, a_n + 99, 4);
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 0; top: ' + (a_n + 118) + 'px; width: 765; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 152; top: ' + (a_n + 118) + 'px; width: 613; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 264; top: ' + (a_n + 118) + 'px; width: 501; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 400; top: ' + (a_n + 118) + 'px; width: 365; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 536; top: ' + (a_n + 118) + 'px; width: 229; height: 34"><\/div>');
    write_header_item('Patient ID/Hospital ID', '7pt Arial', 6, a_n + 120, 23);
    write_header_item('Patient Birth Date', '7pt Arial', 158, a_n + 120, 18);
    write_header_item('Patient SSN', '7pt Arial', 270, a_n + 120, 23);
    write_header_item('Patient Phone Number', '7pt Arial', 406, a_n + 120, 23);
    write_header_item('Physician', '7pt Arial', 542, a_n + 120, 23);
    write_header_item(s_report.orderresult.person_account_number, 'bold 9pt Arial', 11, a_n + 132, 23);
    write_header_item(s_report.person.birth_date, 'bold 9pt Arial', 163, a_n + 132, 10);
    if ( s_report.person.ssn.length == 9)
    {
        var z_sSSN= s_report.person.ssn.substr(0, 3) + '-' + s_report.person.ssn.substr(3, 2) + '-' + s_report.person.ssn.substr(5,4);
        write_header_item(z_sSSN, 'bold 9pt Arial', 275, a_n + 132, 11);
    }
    if ( s_report.person.home_phone_number.length == 7 )
    {
        var z_sPhone= '(' + s_report.person.home_phone_area_code + ') ' + s_report.person.home_phone_number.substr(0,3) + '-' + s_report.person.home_phone_number.substr(3,4);
        write_header_item(z_sPhone, 'bold 9pt Arial', 411, a_n + 132, 14);
    }
    write_header_item(s_report.orderresult.referring_caregiver_name, 'bold 9pt Arial', 548, a_n + 132, 30);
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 0; top: ' + (a_n + 151) + 'px; width: 765; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 40; top: ' + (a_n + 151) + 'px; width: 725; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 152; top: ' + (a_n + 151) + 'px; width: 613; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 264; top: ' + (a_n + 151) + 'px; width: 501; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 400; top: ' + (a_n + 151) + 'px; width: 365; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 536; top: ' + (a_n + 151) + 'px; width: 229; height: 34"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 672; top: ' + (a_n + 151) + 'px; width: 93; height: 34"><\/div>');
    write_header_item('Page', '7pt Arial', 6, a_n + 153, 5);
    write_header_item('Requisition No.', '7pt Arial', 46, a_n + 153, 15);
    write_header_item('Accession No.', '7pt Arial', 158, a_n + 153, 15);
    write_header_item('Collection Date \& Time', '7pt Arial', 270, a_n + 153, 23);
    write_header_item('Log-in Date \& Time', '7pt Arial', 406, a_n + 153, 20);
    write_header_item('Report Date \& Time', '7pt Arial', 542, a_n + 153, 20);
    write_header_item('REPORT STATUS', '7pt Arial', 678, a_n + 153, 15);
    write_header_item(new String(s_nPageCount + 1), 'bold 9pt Arial', 12, a_n + 165, 5);
    write_header_item(s_report.orderresult.placer_order_number, 'bold 9pt Arial', 51, a_n + 165, 15);
    write_header_item(s_report.orderresult.filler_order_number, 'bold 9pt Arial', 163, a_n + 165, 15);
    write_header_item(s_report.orderresult.collection_datetime, 'bold 9pt Arial', 275, a_n + 165, 20);
    write_header_item(s_report.orderresult.login_datetime, 'bold 9pt Arial', 411, a_n + 165, 20);
    write_header_item(s_report.orderresult.result_datetime, 'bold 9pt Arial', 547, a_n + 165, 20);
    var z_sResultStatus= 'FINAL';
    if (s_report.orderresult.result_status == 'P')
    {
        z_sResultStatus= 'PARTIAL';
    }
    if (s_report.orderresult.result_status == 'C')
    {
        z_sResultStatus= 'CORRECTED';
    }
    if (s_report.orderresult.result_status == 'I')
    {
        z_sResultStatus= 'IN LAB';
    }
    if (s_report.orderresult.result_status == 'X')
    {
        z_sResultStatus= 'CANCELLED';
    }
    if (s_report.orderresult.result_status == 'NA')
    {
        z_sResultStatus= 'N/A';
    }
    write_header_item(z_sResultStatus, 'bold 9pt Arial', 683, a_n + 165, 15);
    addContent('<div style="border: solid 1px black; background-color: rgb(0, 0, 0); position: absolute; margin: 0; padding: 0; left: 0; top: ' + (a_n + 189) + 'px; width: 765; height: 762"><\/div>');
    addContent('<div style="border: solid 1px black; background-color: rgb(255, 255, 255); position: absolute; margin: 0; padding: 0; left: 1;top: ' + (a_n + 208) + 'px; width: 762; height: 730"><\/div>');
    var z_sTestHeader= "TEST RESULTS";
    if ( s_bTextOnly == 0 )
    {
        z_sTestHeader= "TEST";	
        write_header_item_color('IN RANGE', 'bold 8pt Arial', 252, a_n + 192, 12, 'rgb(255, 255, 255)');
        write_header_item_color('OUT OF RANGE', 'bold 8pt Arial', 392, a_n + 192, 12, 'rgb(255, 255, 255)');
        write_header_item_color('REFERENCE RANGE', 'bold 8pt Arial', 511, a_n + 192, 16, 'rgb(255, 255, 255)');
        write_header_item_color('UNITS', 'bold 8pt Arial', 626, a_n + 192, 12, 'rgb(255, 255, 255)');
        addContent('<div style="border: solid 1px black; background-color: rgb(255, 255, 255); position: absolute; margin: 0; padding: 0; left: 245px ;top: ' + (a_n + 208) + 'px; width: 259; height: 730"><\/div>');
        addContent('<div style="border: solid 1px black; background-color: rgb(245, 245, 245); position: absolute; margin: 0; padding: 0; left: 364px ;top: ' + (a_n + 208) + 'px; width: 140; height: 730"><\/div>');
    }
    write_header_item_color(z_sTestHeader, 'bold 8pt Arial', 12, a_n + 192, 12, 'rgb(255, 255, 255)');
    write_header_item_color('SITE', 'bold 6pt Arial', 703, a_n + 190, 4, 'rgb(255, 255, 255)');
    write_header_item_color('CODE', 'bold 6pt Arial', 703, a_n + 198, 4, 'rgb(255, 255, 255)');
}//end function

function blot_range(a_nTop, a_nLeft, a_nWidth, a_nHeight)
{
    addContent('<div style="background-color: rgb(255, 255, 255); position: absolute; margin: 0; padding: 0; left: ' + a_nLeft + 'px; top: ' + a_nTop + 'px; width: ' + a_nWidth + 'px; height: ' + a_nHeight +'px"><\/div>');
}	

function page_check(a_nOffset, a_nCheckLines)
{
    // a_nCheckLines should alwas be > 1
    if ( a_nOffset % s_nPageArea + a_nCheckLines * 12 > s_nBottomEdge )
    {
        return 1;
    }
    else
    {
        return 0;	
    }
}//end function

function new_page_offset()
{
    real_page_count++;
    page_header(s_nPageCount * s_nPageArea);
    return s_nPageCount * s_nPageArea + s_nMainEdge;
}	

function ReportLine(a_nSeq, a_nTestLines, a_sType, a_sTest, a_sAnalyte, a_bAbnormal, a_sStatus, a_sResult, a_sUnits, a_sRefRange, a_sSiteCode, a_sResultStatus, a_sTestCode, a_sAnalyteCode, a_sResultTime, a_sSpecimen, a_sProfile)
{
    this.m_nSeq= a_nSeq;
    this.m_nTestLines= a_nTestLines;
    this.m_sType= a_sType;
    this.m_sTest= trim(a_sTest);
    this.m_sAnalyte= trim(a_sAnalyte);
    this.m_bAbnormal= (a_bAbnormal == undefined)? 0: a_bAbnormal;
    this.m_sStatus= (a_sStatus == undefined)? '': a_sStatus;
    this.m_sResult= (a_sResult == undefined)? '': a_sResult;
    this.m_sUnits= (a_sUnits == undefined)? '': a_sUnits;
    this.m_sRefRange= (a_sRefRange == undefined)? '': a_sRefRange;
    this.m_sSiteCode= (a_sSiteCode == undefined)? '': a_sSiteCode;
    this.m_nIndentAnalyte= 0;
    this.m_nIndentText= 0;
    this.m_nBlank= 0;
    this.m_nSuppressAnalyte= 0;
    this.m_sResultStatus= (a_sResultStatus == undefined)? '': a_sResultStatus;
    this.m_nHeadLine= 0;
    this.m_sTestCode= (a_sTestCode == undefined)? '': a_sTestCode;
    this.m_sAnalyteCode= (a_sAnalyteCode == undefined)? '': a_sAnalyteCode;
    this.m_sResultTime= (a_sResultTime == undefined)? '': a_sResultTime;
    this.m_sSpecimen= (a_sSpecimen == undefined)? '': a_sSpecimen;
    this.m_sProfile= (a_sProfile == undefined)? '': a_sProfile;
    this.m_bShowProfile= 0;
}//end ctor

function Block(a_x, a_n)
{
    this.m_x= a_x;
    this.m_n= a_n;
}//end ctor

function Lab(a_sCode, a_sName, a_sAddress_1, a_sAddress_2, a_sCity, a_sState, a_sZip, a_sDirector, a_sPhone, a_sCliaNumber)
{
    this.m_sCode= a_sCode;
    this.m_sName= a_sName;
    this.m_sAddress_1= a_sAddress_1;
    this.m_sAddress_2= a_sAddress_2;
    this.m_sCity= a_sCity;
    this.m_sState= a_sState;
    this.m_sZip= a_sZip;
    this.m_sDirector= (a_sDirector == undefined)? '': a_sDirector;
    this.m_sPhone= (a_sPhone == undefined)? '': a_sPhone;
    this.m_sCliaNumber= (a_sCliaNumber == undefined)? '': a_sCliaNumber;
}//end ctor

function Columns(a_xnTest, a_xnStatus, a_xnInRange, a_xnOutRange, a_xnRefRange, a_xnUnits, a_xnSite)
{	
    this.m_xnTest= a_xnTest;
    this.m_xnStatus= a_xnStatus;
    this.m_xnInRange= a_xnInRange;
    this.m_xnOutRange= a_xnOutRange;
    this.m_xnRefRange= a_xnRefRange;
    this.m_xnUnits= a_xnUnits;
    this.m_xnSite= a_xnSite;
}//end ctor

function report_columns()
{
    return new Columns(new Block(7,231), new Block(372,14), new Block(252,105), new Block(392,105), new Block(511,112), new Block(626, 70), new Block(703,56));
}//end function

function word_wrap(a_sText, a_nWidth, a_pLines, a_sHyphen, a_nLead, a_nIndent)
{
    var z_nLeading= a_nWidth * (a_nLead/100);
    var j= 0;
    for(var z_pos= 0; z_pos < a_sText.length; j++)
    {
        var z_nRealWidth= z_pos > 0? a_nWidth - a_nIndent: a_nWidth;
        var n= a_sText.indexOf('\n', z_pos);
        if ( n != -1 && n < z_pos + z_nRealWidth)
        {
            if ( n == z_pos )
            {
                a_pLines[j]= '';
            }
            else
            {
                a_pLines[j]= a_sText.substr(z_pos, n - z_pos);
            }
            z_pos= n + 1;		
        }
        else if (z_pos + z_nRealWidth < a_sText.length)
        {
            var k= a_sText.lastIndexOf(' ', z_pos + z_nRealWidth);
            if (k == -1 || k + z_nLeading < z_pos + z_nRealWidth)	
            {
                // Hyphenate
                a_pLines[j]= a_sText.substr(z_pos, z_nRealWidth - 1) + a_sHyphen;
                z_pos += (z_nRealWidth - 1);
            }
            else
            {
                a_pLines[j]= a_sText.substr(z_pos, k - z_pos);
                z_pos= k + 1;
            }	
        }
        else
        {
            a_pLines[j]= a_sText.substr(z_pos);
            z_pos += z_nRealWidth;
        }
    }//end for
}//end function

function write_item(a_sText, a_nLeft, a_nTop, a_bBold, a_bUnderline, a_bBlot)
{
    var z_sBold= 'bold';
		var color = 'rgb(0, 0, 0)';
    if (a_bBold == 0)
    {
        z_sBold= '';
    }
    if (a_bBlot != 0)	
    {
        blot_range(a_nTop + 2, a_nLeft, a_sText.length * 7, 10);
    }
		
		if (a_nLeft == 372 || a_nLeft == 392) {
			color = 'rgb(255, 0, 0)';
			z_sBold= 'bold';
		}		
		
    if (a_bUnderline == 0)
    {
        addContent('<pre style="font: ' + z_sBold + ' 9pt Courier New; color: '+ color +'; position: absolute; margin: 0; padding: 0;  left: ' + a_nLeft + 'px; top: ' + a_nTop + 'px">' + escape_text(a_sText) + '<\/pre>');
    }
    else
    {
        addContent('<pre style="text-decoration: underline; font: ' + z_sBold + ' 9pt Courier New; color: rgb(0, 0, 0); position: absolute; margin: 0; padding: 0;  left: ' + a_nLeft + 'px; top: ' + a_nTop + 'px">' + escape_text(a_sText) + '<\/pre>');
    }
}//end function

function line_st(a_nOffset, a_rLine)
{
    var z_cols= report_columns();
    var z_indent= a_rLine.m_nIndentAnalyte;
    var z_nOffset= a_nOffset + 12;
    // This assumes that m_sTest and m_sAnalyte have been "trimmed" AND that sequence is zero in ONLY those cases where there is but one analyte
    if (a_rLine.m_nSeq == 0)
    {
        z_nOffset += 12;
        if ( page_check(z_nOffset, 3) != 0 )
        {
            write_item('\>\> REPORT CONTINUED ON NEXT PAGE \<\<', 258, z_nOffset + 12, 0, 0, 1);
            z_nOffset= new_page_offset();
        }
        if ( a_rLine.m_sProfile.length && a_rLine.m_bShowProfile )
        {
            write_item(a_rLine.m_sProfile, z_cols.m_xnTest.m_x, z_nOffset, 0, 1, 0);
            z_nOffset += 18;
        }
        if ( !istrcomp(a_rLine.m_sTest, a_rLine.m_sAnalyte) || a_rLine.m_nTestLines > 1)
        {
            if ( a_rLine.m_sProfile.length )
            {
                write_item(a_rLine.m_sTest, z_cols.m_xnTest.m_x + 21, z_nOffset, 0, 0, 0);
            }
            else
            {
                write_item(a_rLine.m_sTest, z_cols.m_xnTest.m_x, z_nOffset, 0, 1, 0);
            }
            z_nOffset += 18;
        }
    }
    else
    {
        if ( page_check(z_nOffset, 2) != 0 )
        {
            write_item('\>\> REPORT CONTINUED ON NEXT PAGE \<\<', 258, z_nOffset + 12, 0, 0, 1);
            z_nOffset= new_page_offset();
            // Throw continuation header onto next page
            write_item(a_rLine.m_sTest + ' (CONTINUED)', z_cols.m_xnTest.m_x, z_nOffset + 12, 0, 1, 1);
            z_nOffset += 30;
        }//end if
        z_nOffset += a_rLine.m_nBlank * 12;
    }//end if-else
    a_nOffset= z_nOffset;
    if (a_rLine.m_nSuppressAnalyte == 0)
    {
        // Courier New 9pt has 7px per char
        if (a_rLine.m_sAnalyte.length * 7  + z_indent > z_cols.m_xnTest.m_n)
        {
            // Ruh-roh - need to wrap
            var z_pALines= new Array();
            word_wrap(a_rLine.m_sAnalyte, (z_cols.m_xnTest.m_n - z_indent)/7, z_pALines, '-', 60, 2);
            var z_nAtmp= a_nOffset;
            write_item(z_pALines[0], z_cols.m_xnTest.m_x + z_indent, z_nAtmp, a_rLine.m_bAbnormal, 0, 0);
            z_nAtmp += 12;
            for(var k= 1; k < z_pALines.length; k++, z_nAtmp += 12)
            {
                write_item(z_pALines[k], z_cols.m_xnTest.m_x + z_indent + 14, z_nAtmp, a_rLine.m_bAbnormal, 0, 0);
            }//end for
            z_nOffset= z_nAtmp - 4;	
        }
        else
        {
            write_item(a_rLine.m_sAnalyte, z_cols.m_xnTest.m_x + z_indent, a_nOffset, a_rLine.m_bAbnormal, 0, 0);
        }//end if-else
        if (a_rLine.m_sStatus.length > 0 && a_rLine.m_bAbnormal != 0)
        {
            write_item(a_rLine.m_sStatus, z_cols.m_xnStatus.m_x, a_nOffset, a_rLine.m_bAbnormal, 0, 0);
        }
    }//end if
    var z_nValueStart= z_cols.m_xnInRange.m_x;
    var z_nValueEdge= z_cols.m_xnInRange.m_x + z_cols.m_xnInRange.m_n;
    var z_bBlot= 1;
    if ( a_rLine.m_bAbnormal == 1 )
    {
        z_bBlot= 0;
        z_nValueStart= z_cols.m_xnOutRange.m_x;
        z_nValueEdge= z_cols.m_xnOutRange.m_x + z_cols.m_xnOutRange.m_n;
    }
    var z_nRefEdge= z_cols.m_xnRefRange.m_x + z_cols.m_xnRefRange.m_n;
    if (a_rLine.m_sUnits.length == 0)
    {
        z_nRefEdge= z_cols.m_xnUnits.m_x + z_cols.m_xnUnits.m_n;
    }
    else
    {
        if ( a_rLine.m_sUnits.length * 7 > z_cols.m_xnUnits.m_n )
        {
            var z_pUnitLines= new Array();
            word_wrap(a_rLine.m_sUnits, (z_cols.m_xnUnits.m_n)/7, z_pUnitLines, '\\', 0, 2);
            var z_nUtmp= a_nOffset;
            write_item(z_pUnitLines[0], z_cols.m_xnUnits.m_x, z_nUtmp, a_rLine.m_bAbnormal, 0, 0);
            z_nUtmp += 12;
            for(var mm= 1; mm < z_pUnitLines.length; mm++, z_nUtmp += 12)
            {
                write_item(z_pUnitLines[mm], z_cols.m_xnUnits.m_x + 14, z_nUtmp, a_rLine.m_bAbnormal, 0, 0);
            }//end for
            if (z_nOffset < z_nUtmp - 6)
            {
                z_nOffset= z_nUtmp - 6;
            }
        }
        else
        {	
            write_item(a_rLine.m_sUnits, z_cols.m_xnUnits.m_x, a_nOffset, a_rLine.m_bAbnormal, 0, 0);
        }//end if-else						
    }//end if-else				
    if ( a_rLine.m_sRefRange.length == 0 )
    {
        z_bBlot= 1;
        z_nValueEdge= z_nRefEdge;
    }
    else
    {				
        if (a_rLine.m_sRefRange.length * 7 > z_nRefEdge - z_cols.m_xnRefRange.m_x)
        {
            var z_pFLines= new Array();
            word_wrap(a_rLine.m_sRefRange, (z_nRefEdge - z_cols.m_xnRefRange.m_x)/7, z_pFLines, '\\', 50, 2);
            var z_nFtmp= a_nOffset;
            write_item(z_pFLines[0], z_cols.m_xnRefRange.m_x, z_nFtmp, a_rLine.m_bAbnormal, 0, 0);
            z_nFtmp += 12;
            for(var m= 1; m < z_pFLines.length; m++, z_nFtmp += 12)
            {
                write_item(z_pFLines[m], z_cols.m_xnRefRange.m_x + 14, z_nFtmp, a_rLine.m_bAbnormal, 0, 0);
            }//end for
            if (z_nOffset < z_nFtmp - 6)
            {
                z_nOffset= z_nFtmp - 6;
            }
        }
        else
        {	
            write_item(a_rLine.m_sRefRange, z_cols.m_xnRefRange.m_x, a_nOffset, a_rLine.m_bAbnormal, 0, 0);
        }//end if-else
    }//end if-else
    if (a_rLine.m_sResult.length * 7 > (z_nValueEdge - z_nValueStart) )
    {
        var z_pRLines= new Array();
        word_wrap(a_rLine.m_sResult, (z_nValueEdge - z_nValueStart)/7, z_pRLines, ' ', 50, 2);
        var z_nRtmp= a_nOffset;
        for(var h= 0; h < z_pRLines.length; h++, z_nRtmp += 12)
        {
            if ( page_check(z_nRtmp, 2) != 0 )
            {
                write_item('\>\> REPORT CONTINUED ON NEXT PAGE \<\<', 258, z_nRtmp + 12, 0, 0, 1);
                z_nRtmp= new_page_offset();
                // Throw continuation header onto next page
                write_item(a_rLine.m_sTest + ' (CONTINUED)', z_cols.m_xnTest.m_x, z_nRtmp + 12, 0, 1, 1);
                z_nRtmp += 24;
            }//end if
            write_item(z_pRLines[h], z_nValueStart, z_nRtmp, a_rLine.m_bAbnormal, 0, z_bBlot);
        }//end for
        if (z_nOffset < z_nRtmp - 6)
        {
            z_nOffset= z_nRtmp - 6;
        }	
    }
    else	
    {
        write_item(a_rLine.m_sResult, z_nValueStart, a_nOffset, a_rLine.m_bAbnormal, 0, z_bBlot);
    }
    if (a_rLine.m_nSuppressAnalyte == 0 && a_rLine.m_sSiteCode.length > 0)
    {
        if ( a_rLine.m_sSiteCode.length > z_cols.m_xnSite.m_n/7 )
        {
            write_item(a_rLine.m_sSiteCode.substr(0, z_cols.m_xnSite.m_n/7), z_cols.m_xnSite.m_x, a_nOffset, a_rLine.m_bAbnormal, 0, 0);
        }
        else
        {
            write_item(a_rLine.m_sSiteCode, z_cols.m_xnSite.m_x, a_nOffset, a_rLine.m_bAbnormal, 0, 0);
        }//end if-else
    }//end if					
    return z_nOffset;	
}//end function

function line_nm(a_nOffset, a_rLine)
{
    return line_st(a_nOffset, a_rLine);	
}//end function

function line_blank(a_nOffset, a_rLine)
{
    var z_cols= report_columns();
    var z_nOffset= a_nOffset + 12;
    if (a_rLine.m_nSeq == 0)
    {
        z_nOffset += 12;
        if ( page_check(z_nOffset, 3) != 0 )
        {
            write_item('\>\> REPORT CONTINUED ON NEXT PAGE \<\<', 258, z_nOffset + 12, 0, 0, 1);
            z_nOffset= new_page_offset();
        }
        if ( a_rLine.m_sProfile.length && a_rLine.m_bShowProfile )
        {
            write_item(a_rLine.m_sProfile, z_cols.m_xnTest.m_x, z_nOffset, 0, 1, 0);
            z_nOffset += 18;
        }
        if ( !istrcomp(a_rLine.m_sTest, a_rLine.m_sAnalyte) || a_rLine.m_nTestLines > 1)
        {
            if ( a_rLine.m_sProfile.length )
            {
                write_item(a_rLine.m_sTest, z_cols.m_xnTest.m_x + 21, z_nOffset, 0, 0, 0);
            }
            else
            {
                write_item(a_rLine.m_sTest, z_cols.m_xnTest.m_x, z_nOffset, 0, 1, 0);
            }
            z_nOffset += 18;
        }
    }
    else
    {
        if ( page_check(a_nOffset, 2) != 0 )
        {
            write_item('\>\> REPORT CONTINUED ON NEXT PAGE \<\<', 258, z_nOffset + 12, 0, 0, 1);
            z_nOffset= new_page_offset();
            // Throw continuation header onto next page
            write_item(a_rLine.m_sTest + ' (CONTINUED)', z_cols.m_xnTest.m_x, z_nOffset + 12, 0, 1, 1);
            z_nOffset += 30;
        }//end if
    }//end if-else
    return z_nOffset;		
}//end function	
		
function line_tx(a_nOffset, a_rLine)
{
    var z_cols= report_columns();
    var z_nOffset= a_nOffset + 12;
    // This assumes that m_sTest and m_sAnalyte have been "trimmed" AND that sequence is zero in ONLY those cases where there is but one analyte
    if (a_rLine.m_nSeq == 0)
    {
        if ( page_check(a_nOffset, 3) != 0 )
        {
            write_item('\>\> REPORT CONTINUED ON NEXT PAGE \<\<', 258, z_nOffset + 12, 0, 0, 1);
            z_nOffset= new_page_offset();
        }
        z_nOffset += 12;
        if ( a_rLine.m_sProfile.length && a_rLine.m_bShowProfile )
        {
            write_item(a_rLine.m_sProfile, z_cols.m_xnTest.m_x, z_nOffset, 0, 1, 1);
            z_nOffset += 18;
        }
        if ( !istrcomp(a_rLine.m_sTest, a_rLine.m_sAnalyte) || a_rLine.m_nTestLines > 1)
        {
            if ( a_rLine.m_sProfile.length )
            {
                write_item(a_rLine.m_sTest, z_cols.m_xnTest.m_x + 21, z_nOffset, 0, 0, 1);
            }
            else
            {
                write_item(a_rLine.m_sTest, z_cols.m_xnTest.m_x, z_nOffset, 0, 1, 1);
            }
        }
        z_nOffset += 18;
        if (a_rLine.m_sSiteCode.length > 0)
        {
            if ( a_rLine.m_sSiteCode.length > z_cols.m_xnSite.m_n/7)
            {
                write_item(a_rLine.m_sSiteCode.substr(0, z_cols.m_xnSite.m_n/7), z_cols.m_xnSite.m_x, z_nOffset, 0, 0, 1);
            }
            else
            {
                write_item(a_rLine.m_sSiteCode, z_cols.m_xnSite.m_x, z_nOffset, 0, 0, 1);
            }//end if-else
        }//end if					
    }
    else
    {
        if ( page_check(z_nOffset, 2) != 0 )
        {
            write_item('\>\> REPORT CONTINUED ON NEXT PAGE \<\<', 258, z_nOffset + 12, 0, 0, 1);
            z_nOffset= new_page_offset();
            // Throw continuation header onto next page
            write_item(a_rLine.m_sTest + ' (CONTINUED)', z_cols.m_xnTest.m_x, z_nOffset + 12, 0, 1, 1);
            z_nOffset += 30;
        }//end if
    }//end if-else
    z_nOffset += a_rLine.m_nBlank * 6;
    if ( a_rLine.m_nSuppressAnalyte == 0 )
    {
        write_item(a_rLine.m_sAnalyte, z_cols.m_xnTest.m_x + a_rLine.m_nIndentAnalyte, z_nOffset, a_rLine.m_bAbnormal, 1, 0);
        if (a_rLine.m_sSiteCode.length > 0 && a_rLine.m_nSeq > 0)
        {
            if ( a_rLine.m_sSiteCode.length > z_cols.m_xnSite.m_n/7)
            {
                write_item(a_rLine.m_sSiteCode.substr(0, z_cols.m_xnSite.m_n/7), z_cols.m_xnSite.m_x, z_nOffset, 0, 0, 1);
            }
            else
            {
                write_item(a_rLine.m_sSiteCode, z_cols.m_xnSite.m_x, z_nOffset, 0, 0, 1);
            }//end if-else
        }//end if					

        z_nOffset += 18;
    }
    var z_nRightEdge= z_cols.m_xnUnits.m_x + z_cols.m_xnUnits.m_n;
    if (a_rLine.m_sResult.length * 7 > z_nRightEdge - (z_cols.m_xnTest.m_x + a_rLine.m_nIndentText) || a_rLine.m_sResult.indexOf('\n') != -1)
    {
        // Ruh-roh - need to wrap
        var z_pLines= new Array();
        word_wrap(a_rLine.m_sResult, (z_nRightEdge - (z_cols.m_xnTest.m_x + a_rLine.m_nIndentText))/7, z_pLines, '-', 25, 0);
        var z_tmp= z_nOffset;
        for(var k= 0; k < z_pLines.length; k++, z_tmp += 12)
        {
            if ( page_check(z_tmp, 2) != 0 )
            {
                write_item('\>\> REPORT CONTINUED ON NEXT PAGE \<\<', 258, z_tmp + 12, 0, 0, 1);
                z_tmp= new_page_offset();
                // Throw continuation header onto next page
                write_item(a_rLine.m_sTest + ' (CONTINUED)', z_cols.m_xnTest.m_x, z_tmp + 12, 0, 1, 1);
                z_tmp += 24;
            }//end if
            write_item(z_pLines[k], z_cols.m_xnTest.m_x + a_rLine.m_nIndentText, z_tmp, 0, 0, 1);
        }//end for
        z_nOffset= z_tmp;	
    }
    else
    {
        write_item(a_rLine.m_sResult, z_cols.m_xnTest.m_x + a_rLine.m_nIndentText, z_nOffset, 0, 0, 1);
    }//end if-else
    return z_nOffset;		
}//end function

function line_lab(a_nOffset, a_rLab, a_nLabCodeMax)
{
    var z_cols= report_columns();
    z_nOffset= a_nOffset + 12
    if ( page_check(z_nOffset, 6) != 0 )
    {
        write_item('\>\> REPORT CONTINUED ON NEXT PAGE \<\<', 258, z_nOffset + 12, 0, 0, 1);
        z_nOffset= new_page_offset();
        write_item('NOTE: (CONTINUED)', z_cols.m_xnTest.m_x, z_nOffset + 12, 0, 1, 1);
        z_nOffset += 24;
    }
    var z_sRefLab= '\'' + a_rLab.m_sCode + '\' refers to site:    ';
    var z_nRefLabSize= z_sRefLab.length + (a_nLabCodeMax - a_rLab.m_sCode.length);
    z_nOffset += 12;	
    write_item(z_sRefLab, z_cols.m_xnTest.m_x, z_nOffset, 0, 0, 1);
    if ( a_rLab.m_sCliaNumber.length != 0 )
    {
        write_item(a_rLab.m_sName + ' - CLIA# ' + a_rLab.m_sCliaNumber, z_cols.m_xnTest.m_x + z_nRefLabSize * 7, z_nOffset, 0, 0, 1); 
    }
    else
    {
        write_item(a_rLab.m_sName, z_cols.m_xnTest.m_x + z_nRefLabSize * 7, z_nOffset, 0, 0, 1);
    }
    z_nOffset += 12;
    write_item(a_rLab.m_sAddress_1 + ' ' + a_rLab.m_sAddress_2, z_cols.m_xnTest.m_x + z_nRefLabSize * 7, z_nOffset, 0, 0, 1);
    z_nOffset += 12;
    write_item(a_rLab.m_sCity + ' ' + a_rLab.m_sState + ' ' + a_rLab.m_sZip, z_cols.m_xnTest.m_x + z_nRefLabSize * 7, z_nOffset, 0, 0, 1);
    if (a_rLab.m_sPhone.length == 7)
    {
        z_nOffset += 12;
        write_item(a_rLab.m_sPhone.substr(0,3) + '-' + a_rLab.m_sPhone.substr(3,4), z_cols.m_xnTest.m_x + z_nRefLabSize * 7, z_nOffset, 0, 0, 1);
    }
    else if (a_rLab.m_sPhone.length == 10)	
    {
        z_nOffset += 12;
        write_item('\(' + a_rLab.m_sPhone.substr(0,3) + '\) ' + a_rLab.m_sPhone.substr(3,3) + '-' + a_rLab.m_sPhone.substr(6,4), z_cols.m_xnTest.m_x + z_nRefLabSize * 7, z_nOffset, 0, 0, 1);
    }//end if-else
    if ( a_rLab.m_sDirector.length > 0 )
    {
        z_nOffset += 12;
        write_item("Director: " + a_rLab.m_sDirector, z_cols.m_xnTest.m_x + z_nRefLabSize * 7, z_nOffset, 0, 0, 1);
    }
    return z_nOffset + 12;
}//end function

function isTX(a_sType)
{
    if ( a_sType == 'NM' || a_sType == 'ST' || a_sType == 'PC' )
    {
        return 0;
    }
    return 1;
}	

function line(a_nOffset, a_rLine)
{
    if ( a_rLine.m_sResult == 'DNR' )
    {
        return a_nOffset;//skip
    }
    if ( a_rLine.m_sResult == 'TNP' )
    {
        a_rLine.m_sResult= 'TEST NOT PERFORMED';
    }
    if ( a_rLine.m_sResult == 'QNS' )
    {
        a_rLine.m_sResult= 'QUANTITY NOT SUFFICIENT';
    }
    if (a_rLine.m_sType == 'ST')
    {
        return line_st(a_nOffset, a_rLine);
    }
    if (a_rLine.m_sType == 'NM')
    {
        return line_nm(a_nOffset, a_rLine);
    } 
    if (a_rLine.m_sType == 'TX')
    {
        return line_tx(a_nOffset, a_rLine);
    }
    if (a_rLine.m_sType == 'BLANK')
    {
        if (a_rLine.m_sResultStatus == 'X' )
        {
            a_rLine.m_sResult= 'Test Cancelled';
            a_rLine.m_sType= 'NTE';
            return line_tx(a_nOffset, a_rLine);
        }
        return line_blank(a_nOffset, a_rLine);
    }	
    // Anything not covered above is assumed to be "text"
    return line_tx(a_nOffset, a_rLine);
}//end function

function process_lines(a_nOffset, a_pLines, a_pLabs)
{
    var z_pLast= new ReportLine(0,0,'','','',0,'','','','','');
    var z_sFirstAnalyte= '';
    var z_bIdentical= 0;
    for (var ra= 0; ra < a_pLines.length; ra++)
    {
        if ( a_pLines[ra].m_sType == 'BLANK' && a_pLines[ra].m_sResultStatus == 'I' )
        {
            a_pLines[ra].m_sType= 'ST';
            a_pLines[ra].m_sResult= 'PENDING';
        }
        if ( a_pLines[ra].m_sType == 'BLANK' && a_pLines[ra].m_sResultStatus == 'P' )
        {
            a_pLines[ra].m_sType= 'ST';
            a_pLines[ra].m_sResult= 'PENDING';// QUEST SPECIAL REQUEST
        }
        if ( a_pLines[ra].m_sType == 'TX' && (a_pLines[ra].m_sRefRange.length > 0 || a_pLines[ra].m_sUnits.length > 0) )
        {
            a_pLines[ra].m_sType= 'ST';
        }
        a_pLines[ra].m_nHeadLine= ra;	
        if ( a_pLines[ra].m_nSeq > 0 )
        {
            a_pLines[ra].m_nHeadLine= a_pLines[ra - 1].m_nHeadLine;
            if ( !istrcomp(z_sFirstAnalyte, a_pLines[ra].m_sAnalyte) )
            {
                z_bIdentical= 0;
            }
            if ( ra + 1 == a_pLines.length && z_bIdentical == 1 )
            {
                a_pLines[a_pLines[ra - 1].m_nHeadLine].m_nTestLines= 1;
            }
        }
        else
        {
            if ( z_bIdentical == 1 )
            {
                a_pLines[a_pLines[ra - 1].m_nHeadLine].m_nTestLines= 1;
            }
            z_bIdentical= 1;
            z_sFirstAnalyte= a_pLines[ra].m_sAnalyte;
        }
        if ( a_pLines[ra].m_sResult == 'DNR' && a_pLines[a_pLines[ra].m_nHeadLine].m_nTestLines > 1 )
        {
            a_pLines[a_pLines[ra].m_nHeadLine].m_nTestLines--;
        }
        if ( a_pLines[ra].m_nSeq > 0 && a_pLines[ra - 1].m_sResult == 'DNR' && a_pLines[ra - 1].m_nSeq == 0)
        {
            a_pLines[ra].m_nSeq= 0;
        }
    }
    var z_nIndentAnalyte= 0;
    var z_nIndentText= 0;
    var z_nIndentNote= 0;
    var z_nProfileIndent= 0;
    var z_sLastProfile= '';
    for (var r= 0; r < a_pLines.length; r++)
    {
        a_pLines[r].m_nTestLines= a_pLines[a_pLines[r].m_nHeadLine].m_nTestLines;
        if ( a_pLines[r].m_nSeq == 0 )
        {
            if ( !istrcomp(z_sLastProfile, a_pLines[r].m_sProfile) )
            {
                z_sLastProfile= a_pLines[r].m_sProfile;
                a_pLines[r].m_bShowProfile= 1;
            }
            if ( z_sLastProfile.length > 0 )
            {
                z_nProfileIndent= 21;
            }
            else
            {
                z_nProfileIndent= 0;
            }
            if ( !isTX(a_pLines[r].m_sType) )
            {
                z_nIndentAnalyte= 21;
                z_nIndentText= 21;
                s_bTextOnly= 0;
                if ( istrcomp(a_pLines[r].m_sTest, a_pLines[r].m_sAnalyte) && a_pLines[r].m_nTestLines == 1 )
                {
                    z_nIndentAnalyte= 0;
                    z_nIndentText= 0;
                }
            }
            else
            {
                z_nIndentAnalyte= 21;
                z_nIndentText= 42;
                if ( istrcomp(a_pLines[r].m_sTest, a_pLines[r].m_sAnalyte) && a_pLines[r].m_nTestLines == 1 )
                {
                    z_nIndentAnalyte= 0;
                    z_nIndentText= 21;
                }
            }//end if-else
            if ( a_pLines[r].m_sType == 'NTE' )
            {
                z_nIndentNote= z_nIndentText;
            }
            else
            {
                z_nIndentNote= z_nIndentText + 21;
            }
            a_pLines[r].m_nIndentAnalyte= z_nIndentAnalyte + z_nProfileIndent;
            a_pLines[r].m_nIndentText= z_nIndentText + z_nProfileIndent;
        }
        else
        {
            if ( !isTX(a_pLines[r].m_sType) )
            {
                s_bTextOnly= 0;
                a_pLines[r].m_nIndentAnalyte= z_nIndentAnalyte + z_nProfileIndent;
                a_pLines[r].m_nIndentText= z_nIndentAnalyte + z_nProfileIndent;
                z_nIndentNote= z_nIndentAnalyte + 21;
                if ( isTX(z_pLast.m_sType) )
                {
                    a_pLines[r].m_nBlank= 1;
                }
                if ( istrcomp(z_pLast.m_sAnalyte, a_pLines[r].m_sAnalyte) )
                {
                    a_pLines[r].m_nSuppressAnalyte= 1;
                }	
            }
            else
            {
                if ( a_pLines[r].m_sType != z_pLast.m_sType )
                {
                    a_pLines[r].m_nBlank= 1;
                }
                if ( a_pLines[r].m_sType == 'NTE' )
                {
                    a_pLines[r].m_nIndentAnalyte= z_nIndentAnalyte + z_nProfileIndent;
                    a_pLines[r].m_nIndentText = z_nIndentNote + z_nProfileIndent;
                }
                else
                {
                    a_pLines[r].m_nIndentAnalyte= z_nIndentAnalyte + z_nProfileIndent;
                    a_pLines[r].m_nIndentText= z_nIndentText + z_nProfileIndent;
                    z_nIndentNote= z_nIndentText + 21;
                }//end if-else
                if ( istrcomp(z_pLast.m_sAnalyte, a_pLines[r].m_sAnalyte) )
                {
                    a_pLines[r].m_nSuppressAnalyte= 1;
                }
            }//end if-else				
        }//end if-else
        z_pLast= a_pLines[r];	
    }//end for
    page_header(0);
    var z_cols= report_columns();
    for (var k= 0; k < a_pLines.length; k++)
    {
        a_nOffset= line(a_nOffset, a_pLines[k]);
    }//end for
    var z_nLabCodeMax= 0;	
    if (a_pLabs.length > 0)	
    {
        if ( page_check(a_nOffset, 7) != 0 )
        {
            a_nOffset += 12;
            write_item('\>\> REPORT CONTINUED ON NEXT PAGE \<\<', 258, a_nOffset + 12, 0, 0, 1);
            a_nOffset= new_page_offset();
        }
        else
        {
            a_nOffset += 12;
        }	
        write_item('NOTE:', z_cols.m_xnTest.m_x, a_nOffset + 12, 0, 1, 1);
        a_nOffset += 12;
    }
    for (var t= 0; t < a_pLabs.length; t++)
    {
        z_nLabCodeMax= a_pLabs[t].m_sCode.length > z_nLabCodeMax? a_pLabs[t].m_sCode.length: z_nLabCodeMax;
    }//end for	
    for (var tt= 0; tt < a_pLabs.length; tt++)
    {
        a_nOffset= line_lab(a_nOffset, a_pLabs[tt], z_nLabCodeMax);		
    }//end for	
    write_item('\>\> END REPORT \<\<', 325, a_nOffset + 12, 0, 0, 1);
	
	if(!$.browser.msie)
	{
		$('#report_all_content_'+real_page_count).css("height", "950px");
	}
	
	var $pagination = $('<div />').addClass('paging');

	if (window.paginate) {
		var txt = 'Displaying ' + (window.currentPage+1) + ' of ' + (real_page_count+1) + ' --- ';

		$pagination.append(txt);

		if (real_page_count > 0) {
			var ct = 0;

			for(ct = 0; ct <= real_page_count; ct++) {

				if (window.currentPage == ct) {
					$pagination.append('<span class="current">'+(window.currentPage+1)+ '</span> &nbsp;' );

					continue;
				}

				$pagination.append('<span><a href="'+window.pageUrl.replace('page:0', 'page:' + (ct+1)) +'">'+(ct+1)+ '</a></span>  &nbsp;' );


			}

			$pagination.append('<span><a href="'+window.pageUrl.replace('page:0', 'page:all') +'"> Show All </a></span>  &nbsp;' );
		}

		
	} else {
		$pagination.append('<span><a href="'+window.pageUrl.replace('page:0', 'page:1') +'"> Split into Pages </a></span>  &nbsp;' );
	}
	
	$pagination.find('a').click(function(){
		window.parent.doScroll();
	});
	
	$(document.body).append($pagination);
	
	


}//end function	
