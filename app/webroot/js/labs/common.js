function writeTests()
{
	var sTestLine = 0;
	var iTest, iICD9, iAOE, iICD9Line;
	var sICD9Text, sAOEText, sAOEQText, sAOEAText;
	var iTestLines, iICD9Lines, iTestLine, iAOELines, iAOELine, iAOEQLine, iAOEALine;
	var sTestCategory = "";
	var bNewCategory = false;
	var bShadeAOE = false;
	var sShadeAOE = "#ffffff";

	document.writeln('<TABLE class="normal100"><TBODY><TR><TD style="width:25px">&nbsp;</td><TD style="width:120px">&nbsp;</td><TD style="width:25px">&nbsp;</td><TD style="width:110px">&nbsp;</td><TD style="width:215px">&nbsp;</td><TD style="width:225px">&nbsp;</td>');

	_totalTests = 0;

	for (iTest in _Tests)
	{
		if (_Tests[iTest].sOrder == _Report.order.order_number)
		{
			_totalTests++;
			sICD9Text = new Array();
			sTestLine = sTestLine + 1;
			iTestLines = 0;
			iICD9Lines = 0;
			iAOELines = 0;

			if ((_Tests[iTest].sCategory) != "" && (_Tests[iTest].sCategory != sTestCategory))
			{
				_iRow++;
				bNewCategory = true;
				sTestCategory = _Tests[iTest].sCategory;
			}

			for (iICD9 in _Tests[iTest].ICD9s)
			{
				for (iICD9Line in _Tests[iTest].ICD9s[iICD9].sText)
				{
					sICD9Text[sICD9Text.length] = _Tests[iTest].ICD9s[iICD9].sText[iICD9Line];
				}
			}

			iICD9Lines = 1;
			iTestLines = _Tests[iTest].sDesc.length;


			sICD9All = sICD9Text.join(', ').replace(/&nbsp;/gi, '').replace(/\,\s/gi, ', &nbsp;');
			//sICD9All = sICD9Al
			
			if (iICD9Lines > iTestLines)
			{
				_iRow += iICD9Lines - 1;
			}
			else
			{
				_iRow += iTestLines - 1;
			}

			checkRow();

			if (bNewCategory)
			{
				bNewCategory = false;

				document.writeln('<TR>');
				document.writeln('<TD style="width:25px">&nbsp;');
				document.writeln('<td style="width:120px">');
				document.writeln('<td style="width:350px; font-weight: bold" colspan=3>' + escHTML(sTestCategory));
			}

			document.writeln('<TR>');
			document.writeln('<TD style="width:25px">' + sTestLine + ')');
			document.writeln('<td style="width:120px; font-weight: bold">' + escHTML(_Tests[iTest].sCode));

			iICD9 = 0;
			iTestLine = 0;
			do
			{
				document.writeln('<td style="width:350px; font-weight: bold" colspan=3>' + _Tests[iTest].sDesc[iTestLine]);
				if (iICD9 < iICD9Lines)
				{
					document.writeln('<td style="width:265px; font-weight: bold">' + sICD9All);
				}
				iICD9++;
				if ((iTestLine + 1) < iTestLines)
				{
					document.writeln('<TR>');
					document.writeln('<TD style="width:25px">');
					document.writeln('<td style="width:120px">');
				}
				
			} while (++iTestLine < iTestLines);

			bShadeAOE = false;
			// trim out AOEs with no answers
			for (iAOE = _Tests[iTest].AOEs.length - 1; iAOE > -1; iAOE--)
			{
				if ("" == _Tests[iTest].AOEs[iAOE].sAOEA)
				{
					_Tests[iTest].AOEs.splice(iAOE, 1);
				}
			}

			for (iAOE in _Tests[iTest].AOEs)
			{
				sAOEQText = _Tests[iTest].AOEs[iAOE].sAOEQText;
				sAOEAText = _Tests[iTest].AOEs[iAOE].sAOEAText;

				iAOELines = sAOEAText.length;

				if (sAOEQText.length > iAOELines)
				{
					iAOELines = sAOEQText.length;
				}

				if (bShadeAOE)
				{
					sShadeAOE = "#d0d0d0";
				}
				else
				{
					sShadeAOE = "#ffffff";
				}
				bShadeAOE = !bShadeAOE;

				// Write blank line before first AOE
				if (iAOE == 0)
				{
					document.writeln('<tr>');
					document.writeln('<td style="width:25px">');
					document.writeln('<td style="width:215px; background: ' + sShadeAOE + '" colspan=3>');
					document.writeln('<td style="width:255px; background: ' + sShadeAOE + '">');
					if (iICD9 < iICD9Lines)
					{
						document.writeln('<td style="width:225px; font-weight: bold">' + sICD9Text[iICD9]);
					}
					_iRow++;
					iICD9++;
				}


				for (iAOELine = 0; iAOELine < iAOELines; iAOELine++)
				{
					if (iICD9 >= iICD9Lines)
					{
						checkRow();
					}
					document.writeln('<tr>');
					document.writeln('<td style="width:25px">');
					if (iAOELine < sAOEQText.length)
					{
						document.writeln('<td style="width:215px; background: ' + sShadeAOE + '" colspan=3>' + sAOEQText[iAOELine]);
					}
					else
					{
						document.writeln('<td style="width:255px; background: ' + sShadeAOE + '" colspan=3>');
					}
					if (iAOELine < sAOEAText.length)
					{
						document.writeln('<td style="width:255px; font-weight: bold; background: ' + sShadeAOE + '">' + sAOEAText[iAOELine]);
					}
					else
					{
						document.writeln('<td style="width:255px; background: ' + sShadeAOE + '">');
					}

					if (iICD9 < iICD9Lines)
					{
						document.writeln('<td style="width:265px; font-weight: bold">' + sICD9Text[iICD9]);
					}
					iICD9++;
				}
			}

			while (iICD9 < iICD9Lines)
			{
				document.writeln('<tr>');
				document.writeln('<td style="width:180px" colspan=3>');
				document.writeln('<td style="width:365px" colspan=2>');
				document.writeln('<td style="width:265px; font-weight: bold">' + sICD9Text[iICD9]);
				iICD9++;
			}

			_iRow++;
			document.writeln('<tr><td colspan=6 style="width:760px">&nbsp;');
		}
	}
}

function testICD9Header()
	{
	document.writeln('<tr><td style="width:760px" colspan=2>');
	document.writeln('<table class="shade" style="border-width: 1px 1px 1px 1px"><tbody><tr>');
	document.writeln('<td style="width:135px"></td>');
	document.writeln('<td align="left" style="width:360px"><b>TEST CODE/DESCRIPTION</B></td>');
	document.writeln('<td align="left" style="width:263px"><b>ICD-9 CODE</B></td>');
	document.writeln('</tr></TBODY></TABLE>');
	document.writeln('</tr></TBODY></TABLE>');
	}