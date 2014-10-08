// Copyright 2011 Nuance Communications, Inc. All rights reserved. 
var NUSAI_version="1.2.100.4606"; 
//v2.0.30511.0
if (!window.Silverlight) window.Silverlight = {}; Silverlight._silverlightCount = 0; Silverlight.__onSilverlightInstalledCalled = false; Silverlight.fwlinkRoot = "http://go2.microsoft.com/fwlink/?LinkID="; Silverlight.__installationEventFired = false; Silverlight.onGetSilverlight = null; Silverlight.onSilverlightInstalled = function() { window.location.reload(false) }; Silverlight.isInstalled = function(b) { if (b == undefined) b = null; var a = false, m = null; try { var i = null, j = false; if (window.ActiveXObject) try { i = new ActiveXObject("AgControl.AgControl"); if (b === null) a = true; else if (i.IsVersionSupported(b)) a = true; i = null } catch (l) { j = true } else j = true; if (j) { var k = navigator.plugins["Silverlight Plug-In"]; if (k) if (b === null) a = true; else { var h = k.description; if (h === "1.0.30226.2") h = "2.0.30226.2"; var c = h.split("."); while (c.length > 3) c.pop(); while (c.length < 4) c.push(0); var e = b.split("."); while (e.length > 4) e.pop(); var d, g, f = 0; do { d = parseInt(e[f]); g = parseInt(c[f]); f++ } while (f < e.length && d === g); if (d <= g && !isNaN(d)) a = true } } } catch (l) { a = false } return a }; Silverlight.WaitForInstallCompletion = function() { if (!Silverlight.isBrowserRestartRequired && Silverlight.onSilverlightInstalled) { try { navigator.plugins.refresh() } catch (a) { } if (Silverlight.isInstalled(null) && !Silverlight.__onSilverlightInstalledCalled) { Silverlight.onSilverlightInstalled(); Silverlight.__onSilverlightInstalledCalled = true } else setTimeout(Silverlight.WaitForInstallCompletion, 3e3) } }; Silverlight.__startup = function() { navigator.plugins.refresh(); Silverlight.isBrowserRestartRequired = Silverlight.isInstalled(null); if (!Silverlight.isBrowserRestartRequired) { Silverlight.WaitForInstallCompletion(); if (!Silverlight.__installationEventFired) { Silverlight.onInstallRequired(); Silverlight.__installationEventFired = true } } else if (window.navigator.mimeTypes) { var b = navigator.mimeTypes["application/x-silverlight-2"], c = navigator.mimeTypes["application/x-silverlight-2-b2"], d = navigator.mimeTypes["application/x-silverlight-2-b1"], a = d; if (c) a = c; if (!b && (d || c)) { if (!Silverlight.__installationEventFired) { Silverlight.onUpgradeRequired(); Silverlight.__installationEventFired = true } } else if (b && a) if (b.enabledPlugin && a.enabledPlugin) if (b.enabledPlugin.description != a.enabledPlugin.description) if (!Silverlight.__installationEventFired) { Silverlight.onRestartRequired(); Silverlight.__installationEventFired = true } } if (!Silverlight.disableAutoStartup) if (window.removeEventListener) window.removeEventListener("load", Silverlight.__startup, false); else window.detachEvent("onload", Silverlight.__startup) }; if (!Silverlight.disableAutoStartup) if (window.addEventListener) window.addEventListener("load", Silverlight.__startup, false); else window.attachEvent("onload", Silverlight.__startup); Silverlight.createObject = function(m, f, e, k, l, h, j) { var d = {}, a = k, c = l; d.version = a.version; a.source = m; d.alt = a.alt; if (h) a.initParams = h; if (a.isWindowless && !a.windowless) a.windowless = a.isWindowless; if (a.framerate && !a.maxFramerate) a.maxFramerate = a.framerate; if (e && !a.id) a.id = e; delete a.ignoreBrowserVer; delete a.inplaceInstallPrompt; delete a.version; delete a.isWindowless; delete a.framerate; delete a.data; delete a.src; delete a.alt; if (Silverlight.isInstalled(d.version)) { for (var b in c) if (c[b]) { if (b == "onLoad" && typeof c[b] == "function" && c[b].length != 1) { var i = c[b]; c[b] = function(a) { return i(document.getElementById(e), j, a) } } var g = Silverlight.__getHandlerName(c[b]); if (g != null) { a[b] = g; c[b] = null } else throw "typeof events." + b + " must be 'function' or 'string'"; } slPluginHTML = Silverlight.buildHTML(a) } else slPluginHTML = Silverlight.buildPromptHTML(d); if (f) f.innerHTML = slPluginHTML; else return slPluginHTML }; Silverlight.buildHTML = function(a) { var b = []; b.push('<object type="application/x-silverlight" data="data:application/x-silverlight,"'); if (a.id != null) b.push(' id="' + Silverlight.HtmlAttributeEncode(a.id) + '"'); if (a.width != null) b.push(' width="' + a.width + '"'); if (a.height != null) b.push(' height="' + a.height + '"'); b.push(" >"); delete a.id; delete a.width; delete a.height; for (var c in a) if (a[c]) b.push('<param name="' + Silverlight.HtmlAttributeEncode(c) + '" value="' + Silverlight.HtmlAttributeEncode(a[c]) + '" />'); b.push("</object>"); return b.join("") }; Silverlight.createObjectEx = function(b) { var a = b, c = Silverlight.createObject(a.source, a.parentElement, a.id, a.properties, a.events, a.initParams, a.context); if (a.parentElement == null) return c }; Silverlight.buildPromptHTML = function(b) { var a = "", d = Silverlight.fwlinkRoot, c = b.version; if (b.alt) a = b.alt; else { if (!c) c = ""; a = "<a href='javascript:Silverlight.getSilverlight(\"{1}\");' style='text-decoration: none;'><img src='{2}' alt='Get Microsoft Silverlight' style='border-style: none'/></a>"; a = a.replace("{1}", c); a = a.replace("{2}", d + "108181") } return a }; Silverlight.getSilverlight = function(e) { if (Silverlight.onGetSilverlight) Silverlight.onGetSilverlight(); var b = "", a = String(e).split("."); if (a.length > 1) { var c = parseInt(a[0]); if (isNaN(c) || c < 2) b = "1.0"; else b = a[0] + "." + a[1] } var d = ""; if (b.match(/^\d+\056\d+$/)) d = "&v=" + b; Silverlight.followFWLink("149156" + d) }; Silverlight.followFWLink = function(a) { top.location = Silverlight.fwlinkRoot + String(a) }; Silverlight.HtmlAttributeEncode = function(c) { var a, b = ""; if (c == null) return null; for (var d = 0; d < c.length; d++) { a = c.charCodeAt(d); if (a > 96 && a < 123 || a > 64 && a < 91 || a > 43 && a < 58 && a != 47 || a == 95) b = b + String.fromCharCode(a); else b = b + "&#" + a + ";" } return b }; Silverlight.default_error_handler = function(e, b) { var d, c = b.ErrorType; d = b.ErrorCode; var a = "\nSilverlight error message     \n"; a += "ErrorCode: " + d + "\n"; a += "ErrorType: " + c + "       \n"; a += "Message: " + b.ErrorMessage + "     \n"; if (c == "ParserError") { a += "XamlFile: " + b.xamlFile + "     \n"; a += "Line: " + b.lineNumber + "     \n"; a += "Position: " + b.charPosition + "     \n" } else if (c == "RuntimeError") { if (b.lineNumber != 0) { a += "Line: " + b.lineNumber + "     \n"; a += "Position: " + b.charPosition + "     \n" } a += "MethodName: " + b.methodName + "     \n" } alert(a) }; Silverlight.__cleanup = function() { for (var a = Silverlight._silverlightCount - 1; a >= 0; a--) window["__slEvent" + a] = null; Silverlight._silverlightCount = 0; if (window.removeEventListener) window.removeEventListener("unload", Silverlight.__cleanup, false); else window.detachEvent("onunload", Silverlight.__cleanup) }; Silverlight.__getHandlerName = function(b) { var a = ""; if (typeof b == "string") a = b; else if (typeof b == "function") { if (Silverlight._silverlightCount == 0) if (window.addEventListener) window.addEventListener("onunload", Silverlight.__cleanup, false); else window.attachEvent("onunload", Silverlight.__cleanup); var c = Silverlight._silverlightCount++; a = "__slEvent" + c; window[a] = b } else a = null; return a }; Silverlight.onRequiredVersionAvailable = function() { }; Silverlight.onRestartRequired = function() { }; Silverlight.onUpgradeRequired = function() { }; Silverlight.onInstallRequired = function() { }; Silverlight.IsVersionAvailableOnError = function(d, a) { var b = false; try { if (a.ErrorCode == 8001 && !Silverlight.__installationEventFired) { Silverlight.onUpgradeRequired(); Silverlight.__installationEventFired = true } else if (a.ErrorCode == 8002 && !Silverlight.__installationEventFired) { Silverlight.onRestartRequired(); Silverlight.__installationEventFired = true } else if (a.ErrorCode == 5014 || a.ErrorCode == 2106) { if (Silverlight.__verifySilverlight2UpgradeSuccess(a.getHost())) b = true } else b = true } catch (c) { } return b }; Silverlight.IsVersionAvailableOnLoad = function(b) { var a = false; try { if (Silverlight.__verifySilverlight2UpgradeSuccess(b.getHost())) a = true } catch (c) { } return a }; Silverlight.__verifySilverlight2UpgradeSuccess = function(d) { var c = false, b = "2.0.31005", a = null; try { if (d.IsVersionSupported(b + ".99")) { a = Silverlight.onRequiredVersionAvailable; c = true } else if (d.IsVersionSupported(b + ".0")) a = Silverlight.onRestartRequired; else a = Silverlight.onUpgradeRequired; if (a && !Silverlight.__installationEventFired) { a(); Silverlight.__installationEventFired = true } } catch (e) { } return c }
/// logger.js /////////////////////////////
///////////////////////////////////////////
var NUSAI_logLevel_Trace = 3; 
var NUSAI_logLevel_Info = 2;
var NUSAI_logLevel_Warning = 1;
var NUSAI_logLevel_Error = 0;
var NUSAI_logLevel = NUSAI_logLevel_Trace; // will be set to Error before Configure() is called; so we trace the startup & then the integrator decides
var NUSAI_indent = 0;

function NUSAI_getNUSAI_indentString(ind) {
    if (!ind)
        ind = NUSAI_indent;
    var str = " ";
    for (var i = 0; i < ind; ++i)
        str += " ";
    return str;
}
function NUSAI_log(method, text, error, level) {
    var errorText = text;
    if (error)
        errorText += ": " + error;

    NUSAI_addServerLogLine(level, method, errorText);
}
function NUSAI_logEnter(method, text) {
    var prefix = "ENTER ";
    NUSAI_logTrace(prefix + method, text);
    NUSAI_indent += 3;
}
function NUSAI_logExit(method) {
    var prefix = "EXIT ";
    NUSAI_indent -= 3;
    NUSAI_logTrace(prefix + method, "");
}
function NUSAI_logTrace(method, text) {
    if (NUSAI_logLevel < 3)
        return;
    if (!text)
        text = "";
    NUSAI_log(NUSAI_getNUSAI_indentString() + method, text, "", NUSAI_logLevel_Trace);
    NUSAI_onInfo(NUSAI_getNUSAI_indentString() + method + " - " + text);
}
function NUSAI_logInfo(method, text) {
    if (NUSAI_logLevel < 2)
        return;
    if (!text)
        text = "";
    NUSAI_log(NUSAI_getNUSAI_indentString() + method, text, "", NUSAI_logLevel_Info);
    NUSAI_onInfo(NUSAI_getNUSAI_indentString() + method + " - " + text);
}
function NUSAI_logWarning(method, text) {
    if (NUSAI_logLevel < 1)
        return;
    if (!text)
        text = "";
    NUSAI_log(NUSAI_getNUSAI_indentString() + method, text, "", NUSAI_logLevel_Warning);
    NUSAI_onInfo(NUSAI_getNUSAI_indentString() + method + " - " + text);
}
function NUSAI_logError(method, text, error) {
    if (!text)
        text = "";
    if (!error)
        error = "";
    NUSAI_log(NUSAI_getNUSAI_indentString() + method, text, error, NUSAI_logLevel_Error);
    NUSAI_onError(NUSAI_getNUSAI_indentString() + method + " - " + text + ":" + error);
}//////////////////////////////////////////////////////////////////////////
// PUBLIC ////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////
/// VARs - to be used in NUSA_configure()
var NUSA_service = "https://nsa-staging.nuancehce.com/basic";
var NUSA_resourceUrl = "https://speechanywhere-staging.nuancehce.com/1.2/";

var NUSA_userId;                    // the Nuance SpeechAnywhere user identifier
var NUSA_applicationName = "NVC";   // the Nuance SpeechAnywhere application name

var NUSA_language = "en";           // the Nuance SpeechAnywhere language

// supported topics
    var NUSA_topicGeneralMedicine = "GeneralMedicine";
    var NUSA_topicInternalMedicine = "InternalMedicine";
    var NUSA_topicSurgery = "Surgery";
    var NUSA_topicMentalHealth = "MentalHealth";
    var NUSA_topicNeurology = "Neurology";
    var NUSA_topicCardiology = "Cardiology";
// the Nuance SpeechAnywhere topic
var NUSA_topic = NUSA_topicGeneralMedicine;

var NUSA_container = "";            // [optional] the HTML container for the Nuance SpeechAnywhere control
                                    // if not set, the control will be inserted as first child of the BODY element
var NUSA_recordButtonBackgroundColor = "white";
var NUSA_recordButtonImage_RecordOn = null;
var NUSA_recordButtonImage_RecordOnHover = null;
var NUSA_recordButtonImage_RecordOff = null;
var NUSA_recordButtonImage_RecordOffHover = null;
var NUSA_recordButtonImage_RecordDisabled = null;
var NUSA_recordButtonImage_RecordDisabledHover = null;
var NUSA_recordButtonHeight = "24px";
var NUSA_recordButtonWidth = "52px";

// enable all text fields for speech recognition (input, textarea, contentEditable)
// is set to false, use data-nusa-enabled=true attribute for certain text fields
var NUSA_enableAll = true;

/// EVENT HANDLER
function NUSA_onRecordingStarted() { }
function NUSA_onRecordingStopped() { }
function NUSA_onProcessingStarted() { }
function NUSA_onProcessingFinished() { }

function NUSA_onError(errorString) { }
function NUSA_onInfo(infoString) { }

/// CLASS DEFINITIONS
var NUSA_focusedElement = "NUSA_focusedElement";
var NUSA_focusableElement = "NUSA_focusableElement";

//////////////////////////////////////////////////////////////////////////
// END PUBLIC ////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////





//////////////////////////////////////////////////////////////////////////
// INTERNAL // INTERNAL // INTERNAL // INTERNAL // INTERNAL // INTERNAL //
//////////////////////////////////////////////////////////////////////////

/// CLASS DEFINITIONS
var NUSAI_classActive = "NUSAI_classActive";
var NUSAI_classResultPending = "NUSAI_classResultPending";


/// EVENT HANDLER
function NUSAI_onInitialized() { }         // called after Nuance SpeechAnywhere was initialized
function NUSAI_onInitializeFailed(error) { } // called if initialization failed
function NUSAI_onDeinitialized() { }         // called after Nuance SpeechAnywhere was closed
function NUSAI_onVuiFormClosed() { }
function NUSAI_onVuiFormCloseFailed() { }
function NUSAI_onSessionClosed() { }
function NUSAI_onSessionCloseFailed() { }
function NUSAI_onFocusChanged(address) { } // the focus of the html element changed
function NUSAI_onFormUpdated() { }
function NUSAI_onActiveChanged(address) { }        // the active Nuance SpeechAnywhere node changed
function NUSAI_onValueChanged(address, value) {}  // the value of the html element changed
function NUSAI_onFeedingAudio() { }
function NUSAI_onRcgBusy() { }
function NUSAI_onRecordingStoppedInternal() { }

/// VARs
var NUSAI_licenseGuid;
var NUSAI_partnerGuid;
var NUSAI_useStaticDomain = false;


var NUSAI_uiMode_Full = 0;
var NUSAI_uiMode_Minimal = 1;
var NUSAI_uiMode_Bubble = 2;
var NUSAI_uiMode = NUSAI_uiMode_Bubble;

var NUSAI_document = document;   // the window.document
var NUSAI_mode_dictation = "dictation";
var NUSAI_mode_navigation = "navigation";
var NUSAI_mode = NUSAI_mode_dictation;         // automatically turn on dictation mode; otherwise command mode is active
var NUSAI_initializeOnLoad = true;    // initialize Nuance SpeechAnywhere after the document was loaded
var NUSAI_sessionId;
var NUSAI_initialRequestId = 0;
var NUSAI_audioFormat = "SPEEX_WB";
var NUSAI_requestId = 0;
var NUSAI_firstEvent = true;
var NUSAI_ids = new Array();
// The last speech-enabled element that had the focus
var NUSAI_lastFocusedSpeechElementId;
// The last element that had the focus
var NUSAI_lastFocusedElement;
var NUSAI_isRecording = false;
var NUSAI_Id = "data-nusa-id";
var NUSAI_logoContainer = "data-nusa-marker-container";
var NUSAI_ConceptName = "data-nusa-concept-name";
var NUSAI_enabled = "data-nusa-enabled";
var NUSAI_protocolVersion = "0.3";
var NUSAI_lastProcessedReplaceTextEvent;
var NUSAI_nodeList;
var NUSAI_fieldHistory = new Array();
var NUSAI_speechFieldInfo = function () {
    this.sfId;
    this.selectionStart;
    this.selectionLength;
    this.text;
    this.utterance;
}
var NUSAI_utteranceQueue = new Array();
var NUSAI_debug = false;
var NUSAI_controlCount = 0;
var NUSAI_utterancePending = 0;
var NUSAI_logDataTreshold = -1;
var NUSAI_bubbleContainer;

var NUSAI_lastFocussedRequestId = -1;
var NUSAI_lastFocussedSpeechFieldRequestId = -1;

var NUSAI_utteranceHistory = new Array();
var NUSAI_utteranceInfo = function () {
    this.selectionStart;
    this.selectionLength;
    this.requestId;
}
var NUSAI_historyRequestIdNeeded = false;
var NUSAI_generatedSfId = 0;

var NUSAI_onPremise = false;

var NUSAI_isMSExplorer = false;
var NUSAI_isMSExplorer9 = false;
var NUSAI_isMozilla = false;
var NUSAI_isSafari = false;
var NUSAI_isChrome = false;

var NUSAI_infoWindow;
var NUSAI_infoWindowTitle = "NuanceSpeechAnywhereInfo";
var NUSAI_infoWindowHeight = 700;
var NUSAI_infoWindowWidth = 600;


var NUSAI_filterList = new Array();
var NUSAI_filterInfo = function () {
    this.key;
    this.value;
}
/// FUNCTIONS
function NUSAI_getXMLEscapeText(text) {
    if (!text || text.length == 0)
        return "";
    var escText = "";
    for (var i = 0; i < text.length; ++i)
        escText += "&#" + text.charCodeAt(i) + ";";
    return escText;
}
function NUSAI_getTokens() {
    try {
        NUSAI_logEnter("NUSAI_getTokens");
        NUSAI_licenseGuid = null;
        NUSAI_partnerGuid = null;

        var tokens = NUSAI_getCookie("NUSA_Guids"); // account token/partner token
        if (!tokens)
            return false;
        var tokenArray = tokens.split('/');
        if (!tokenArray || tokenArray.length < 2)
            return false;

        NUSAI_licenseGuid = tokenArray[0];
        NUSAI_partnerGuid = tokenArray[1];
        NUSAI_logInfo("NUSAI_getTokens", "NUSAI_partnerGuid taken from cookie");
        return true;
    }
    finally {
        NUSAI_logExit("NUSAI_getTokens");
    }
}
function NUSAI_setCookieEx(name, value, expiresMs, path, domain, secure) {
    var today = new Date();
    var expires_date = new Date(today.getTime() + (expiresMs));
    var cookieString = name + "=" + escape(value) +
	       ((expiresMs) ? ";expires=" + expires_date.toGMTString() : "") +
	       ((path) ? ";path=" + path : "") +
	       ((domain) ? ";domain=" + domain : "") +
	       ((secure) ? ";secure" : "");
    NUSAI_document.cookie = cookieString;
}
function NUSAI_setCookie(name, value, expires, path, domain, secure) {
    expires = expires * 60 * 60 * 24 * 1000;
    NUSAI_setCookieEx(name, value, expires, path, domain, secure);
}
function NUSAI_getCookie(name) {
    try {
        NUSAI_logEnter("NUSAI_getCookie", name);

        if (NUSAI_document.cookie == null || NUSAI_document.cookie.length == 0)
            return null;

        var ca = NUSAI_document.cookie.split(';');
        var nameEQ = name + "=";
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ')
                c = c.substring(1, c.length); //delete spaces
            if (c.indexOf(nameEQ) == 0) {
                return unescape(c.substring(nameEQ.length, c.length));
            }
        }
        return null;
    }
    finally {
        NUSAI_logExit("NUSAI_getCookie");
    }
}
function NUSAI_removeCookie(name) {
    NUSAI_setCookieEx(name, "", -1);
}
function NUSAI_storeSessionInfo() {
    var expires = 5 * 60 * 1000; // set to 5min
    NUSAI_setCookieEx("NUSA_userId", NUSA_userId, expires);
    NUSAI_setCookieEx("NUSA_language", NUSA_language, expires);
    NUSAI_setCookieEx("NUSAI_sessionId", NUSAI_sessionId, expires);
    NUSAI_setCookieEx("NUSAI_initialRequestId", NUSAI_GetCurrentRequestId(), expires);    
}
function NUSAI_checkSessionCookie() {
    if (NUSAI_getCookie("NUSA_userId") != NUSA_userId)
        return false;

    if (NUSAI_getCookie("NUSA_language") != NUSA_language)
        return false;

    NUSAI_sessionId = NUSAI_getCookie("NUSAI_sessionId");
    NUSAI_initialRequestId += NUSAI_getCookie("NUSAI_initialRequestId");

    NUSAI_removeCookie("NUSA_userId");
    NUSAI_removeCookie("NUSA_language");
    NUSAI_removeCookie("NUSAI_sessionId");
    NUSAI_removeCookie("NUSAI_initialRequestId");
    return true;
}
function NUSAI_getHtmlLanguage() {
    try {
        var lang = NUSAI_document.getElementsByTagName('html')[0].getAttribute('lang');
        if (!lang)
            lang = NUSAI_document.getElementsByTagName('html')[0].getAttribute('xml:lang');
        return lang;
    }
    catch (error) { }
    return null;
}
function NUSAI_setBrowser() {
    NUSAI_isMSExplorer = navigator.appName.indexOf("Microsoft") >= 0;
    if (NUSAI_isMSExplorer) {
        NUSAI_isMSExplorer9 = navigator.appVersion.indexOf("MSIE 9") >= 0;
        return;
    }

    NUSAI_isMozilla = navigator.appName.indexOf("Netscape") >= 0 || navigator.appName.indexOf("Opera") >= 0;
    if (!NUSAI_isMozilla) {
        alert("Browser detection failed:" + navigator.appName);
        return;
    }
    NUSAI_isChrome = navigator.userAgent.indexOf("Chrome") >= 0;
    if (!NUSAI_isChrome) // chrome userAgent lists both chrome & safari
        NUSAI_isSafari = navigator.userAgent.indexOf("Safari") >= 0;
        
}
function NUSAI_getRuntimeInfo() {
    var runtime = "[Browser:]"+navigator.appName;
    runtime += "[Version:]";
    runtime += navigator.appVersion;
    runtime += "[UserAgent:]";
    runtime += navigator.userAgent;
    runtime += "[Cookies:]";
    runtime += navigator.cookieEnabled;
    runtime += "[Lang:]";
    runtime += navigator.language;
    runtime += "[Platform:]";
    runtime += navigator.platform;
    return runtime;
}
if (window.addEventListener) {
    window.addEventListener('load', NUSAI_onLoad, false);
}
else {
    window.attachEvent('onload', NUSAI_onLoad);
}
if (window.addEventListener) {
    window.addEventListener('beforeunload', NUSAI_onBeforeUnload, false);
}
else {
    window.attachEvent('onbeforeunload', NUSAI_onBeforeUnload);
}
function NUSAI_onBeforeUnload() {
    if (NUSAI_isSafari) {
        NUSAI_unloading();
        return;
    }
    try {
        NUSAI_logEnter("NUSAI_onBeforeUnload");
        NUSAI_updateTextBoxes();
        NUSAI_CloseVuiForm();
        NUSAI_storeSessionInfo();
    }
    catch (error) {
        alert("Error closing:" + error);
        return;
    }
    finally {
        NUSAI_logExit("NUSAI_NUSAI_onBeforeUnload");
    }

    if (NUSAI_infoWindow && !NUSAI_infoWindow.closed)
        NUSAI_infoWindow.close();
}
function NUSAI_getResourcePath(path) {
    if (!NUSA_resourceUrl)
        return path;

    return NUSA_resourceUrl + path;
}
function NUSA_addFilter(key, value) {
    var info = new NUSAI_filterInfo();
    info.key = key;
    info.value = value;
    NUSAI_filterList.push(info);
    
}
function NUSAI_configure() {
    var serviceCookie = NUSAI_getCookie("NUSA_ServerURL");
    if (serviceCookie != null) {
        NUSA_service = serviceCookie;
        var paramIndex = serviceCookie.indexOf("?");
        if (paramIndex >= 0) {
            NUSAI_onPremise = serviceCookie.indexOf("mode=1") >= 0;
            NUSA_service = serviceCookie.substr(0, paramIndex);
        }
    }
    else {
        var paramIndex = NUSA_service.indexOf("?");
        if (paramIndex >= 0) {
            NUSAI_onPremise = NUSA_service.indexOf("mode=1") >= 0;
            NUSA_service = NUSA_service.substr(0, paramIndex);
        }
        else
            NUSAI_onPremise = location.search.indexOf("mode=1") >= 0;
    }

    var resourceCookie = NUSAI_getCookie("NUSA_ResourceURL");
    if (resourceCookie != null)
        NUSA_resourceUrl = resourceCookie;

    if (NUSA_resourceUrl) {
        var trailingChar = NUSA_resourceUrl.charAt(NUSA_resourceUrl.length-1);
        if (trailingChar != '\\' && trailingChar != '/')
            NUSA_resourceUrl += '/';
    }
}
function NUSAI_onLoad() {
    try {
        NUSAI_logEnter("NUSAI_onLoad", "cookie:" + NUSAI_document.cookie);

        if (NUSAI_document.URL.indexOf("http") != 0) {
            alert("Speech recognition is not available because\nthe sample pages and your application are not hosted on a web server.");
            return;
        }

        NUSAI_setBrowser();

        try {
            NUSAI_getTokens();
            var lang = NUSAI_getHtmlLanguage();
            if (lang)
                NUSA_language  = lang;

            NUSAI_logLevel = NUSAI_logLevel_Error; // default value; can be reset in configure()
            NUSA_configure();

            NUSAI_configure();
        }
        catch (error) {
            alert("Error calling 'NUSA_configure':" + error);
            return;
        }

        NUSAI_PrepareElements();

        NUSAI_BuildIdArray();

        if (NUSAI_uiMode == NUSAI_uiMode_Bubble)
            NUSAI_createBubbleGUI();

        NUSAI_checkSessionCookie();
        NUSAI_createDanubeControl();

        if (NUSAI_isMSExplorer && NUSAI_document.NUSABrowserHelper) {
            try {
                NUSAI_document.NUSABrowserHelper.attachEvent("CDEventReceived", NUSAI_OnCDEventReceived);
                NUSAI_document.NUSABrowserHelper.Initialize(NUSAI_document.location.href);
            }
            catch (error) {
                
            }
        }
    }
    finally {
        NUSAI_logExit("NUSAI_onLoad");
    }

}
function NUSAI_clearHistory() {
    NUSAI_utterancePending = 0;
    NUSAI_utteranceQueue = new Array();
    NUSAI_utteranceHistory = new Array();
    NUSAI_fieldHistory = new Array(); 
}
function NUSA_reinitializeVuiForm() {
    NUSAI_logTrace("NUSA_reinitializeVuiForm");

    if (NUSAI_isRecording)
        NUSAI_Stop(-1);

    NUSAI_PrepareElements();
	/*xxxxx NUSAI_BuildIdArray();*/
    NUSAI_BuildIdArray();

    NUSAI_nodeListChanged();
}
function NUSAI_addEventListenerFocusableClick(element) {
    if (element.addEventListener)
        element.addEventListener('click', NUSAI_onElementFocusableClickInternal, false);
    else
        element.attachEvent('onclick', NUSAI_onElementFocusableClickInternal);
}
function NUSAI_addEventListenerFocus(element) {
    if (element.addEventListener)
        element.addEventListener('focus', NUSAI_onElementFocusInternal, false);
    else
        element.attachEvent('onfocus', NUSAI_onElementFocusInternal);
}
function NUSAI_getSpeechFieldId(sfId) {
    if (sfId.charAt(0) == "_")
        return sfId.substr(1);
    return sfId;
}
function NUSAI_getSpeechFieldInfo(sfId) {
    try {
        NUSAI_logEnter("NUSAI_getSpeechFieldInfo", "sfId:" + sfId);
        if (NUSAI_fieldHistory == null) {
            NUSAI_logInfo("NUSAI_getSpeechFieldInfo", "NUSAI_fieldHistory == null");
            return null;
        }

        for (var i = 0; i < NUSAI_fieldHistory.length; ++i) {
            if (NUSAI_fieldHistory[i].sfId == sfId)
                return NUSAI_fieldHistory[i];
        }

        NUSAI_logInfo("NUSAI_getSpeechFieldInfo", "no entry found");
    }
    finally {
        NUSAI_logExit("NUSAI_getSpeechFieldInfo");
    }
    return null;
}

function NUSAI_ignoreElement(element) {
    if (!element.attributes)
        return !NUSA_enableAll;

    var attr = element.attributes[NUSAI_enabled];
    if (!attr)
        return !NUSA_enableAll;

    if (NUSA_enableAll)
        return attr.value.toLowerCase() == "false";
    else
        return attr.value.toLowerCase() != "true";
}
function NUSAI_isContentEditable(element) {
    if (!element.attributes)
        return false;

    return element.attributes["contentEditable"] && element.attributes["contentEditable"].value.toLowerCase() == "true";
}

function NUSAI_PrepareElement(sfId, element) {
    try {
        NUSAI_logEnter("NUSAI_PrepareElement", "sfId:" + sfId);
        element.setAttribute(NUSAI_Id, sfId);
        if (!element.id || element.id == "")
            element.id = sfId;
			

        NUSAI_ids[sfId] = element.id;
    }
    finally {
        NUSAI_logExit("NUSAI_PrepareElement");
    }
}

function NUSAI_PrepareElements() {
    try {
        NUSAI_logEnter("NUSAI_PrepareElements");

        NUSAI_nodeList = "";

        var elements = NUSAI_document.getElementsByTagName("*");
		
        if (elements) {
            for (var i = 0; i < elements.length; ++i) {

                if (NUSAI_ignoreElement(elements[i])) {
                    if (elements[i].attributes && elements[i].attributes[NUSAI_Id])
                        elements[i].setAttribute(NUSAI_Id, "");
                    if (NUSAI_lastFocusedSpeechElementId == elements[i].id) {
                        NUSAI_removeClass(elements[i], NUSA_focusedElement);
                        NUSAI_lastFocusedSpeechElementId = null;
                    }
                    continue;
                }

                var isContentEditable = NUSAI_isContentEditable(elements[i]);

                if ((elements[i].tagName == "INPUT" && (elements[i].type == null || elements[i].type.toLowerCase() == "text")) || isContentEditable || (elements[i].tagName == "TEXTAREA")) 
				{
                    if (!elements[i].attributes || !elements[i].attributes[NUSAI_Id] || elements[i].attributes[NUSAI_Id].value.length==0)
					{
                        var sfId = "0" + NUSAI_getNodeAddressChar(NUSAI_generatedSfId);
                        NUSAI_PrepareElement(sfId, elements[i]);
                        ++NUSAI_generatedSfId;
                    }
                }

                if (NUSAI_isMozilla && isContentEditable) {
                    if (elements[i].innerHTML.length > 0)
                        elements[i].innerHTML = NUSAI_removeSourceBlanks(elements[i].innerHTML);
                }
            }
        }
    }
    finally {
        NUSAI_logExit("ExitMVui_PrepareElements");
    }
}

function NUSAI_BuildIdArray() {
    try {
        NUSAI_logEnter("NUSAI_BuildIdArray");
        
        NUSAI_ids = new Array();
        NUSAI_nodeList = "";
        var counter = 0;
        NUSAI_controlCount = 0;
        var elements = NUSAI_document.getElementsByTagName("*");
        var newlineXml = NUSAI_getXMLEscapeText(NUSAI_getLineBreakText());
        var paragraphXml = NUSAI_getXMLEscapeText(NUSAI_getParagraphText());

        if (elements) {
            for (var i = 0; i < elements.length; ++i) {
                var element = elements[i];

                NUSAI_addEventListenerFocus(element);

                var isContentEditable = NUSAI_isContentEditable(element);

                if (element.nodeName == "INPUT" || element.nodeName == "TEXTAREA" || element.nodeName == "SELECT" || isContentEditable)
                    NUSAI_controlCount++;

                if (element.attributes && element.attributes[NUSAI_Id] && element.attributes[NUSAI_Id].value.length>0) {

                    var sfId = element.attributes[NUSAI_Id].value;

                    if (!element.attributes["id"] || element.attributes["id"].value == "")
                        element.setAttribute("id", sfId);

                    var id = element.attributes["id"].value;
                    NUSAI_ids[sfId] = id;

                    var conceptName = "field" + sfId;
                    
                    if (element.attributes[NUSAI_ConceptName] && element.attributes[NUSAI_ConceptName].value.length > 0)
                        conceptName = element.attributes[NUSAI_ConceptName].value;

                    var valType = "TextType";
                    if (element.type != "text" && element.type != "textarea" && !isContentEditable)
                        valType = "NullType";
                    else 
                        NUSAI_addLogoToElement(element, false);

                    if (!NUSAI_useStaticDomain) {
                        NUSAI_nodeList += "<node Concept='" + conceptName + "' Type='Feature' ValType='" + valType + "' UUID='" + sfId + "'";
                        NUSAI_nodeList += " NewlineFormat='" + newlineXml + "'";
                        if (isContentEditable)
                            NUSAI_nodeList += " ParagraphFormat='" + paragraphXml + "'";
                        else
                            NUSAI_nodeList += " ParagraphFormat='" + newlineXml + newlineXml + "'";

                        NUSAI_nodeList += "/>";                       
                    }
                    if (element.attributes["class"] && element.attributes["class"].value.indexOf(NUSA_focusableElement) >= 0)
                        NUSAI_addEventListenerFocusableClick(element);

                }
                else if (!element.attributes || !element.attributes["id"]) {
                    //element.setAttribute("id", "generated_" + counter);
                    ++counter;
                }
                else 
                    NUSAI_removeLogoFromElement(element);

            }
        }
    }
    finally {
        NUSAI_logExit("NUSAI_BuildIdArray");
    }
}
function NUSAI_removeLogoFromElement(element) {   
    var container = null;
    if (element.attributes && element.attributes[NUSAI_logoContainer]) {
        var containerId = element.attributes[NUSAI_logoContainer].value;
        container = NUSAI_document.getElementById(containerId);
        if (!container)
            alert("Element with id=" + containerId + " not found!\nThis is set as the marker container for element id=" + element.id);
    }

    if (!container)
        container = element;
    else {
        container.visibility = "hidden";
        container.style.display = "none";
    }

    if (container.tagName == "IMG") {
        container.setAttribute("src", "");
        return;
    }
    var cssText = container.style['cssText'];
    var newStyle = true;
    if (cssText && cssText.length > 0) {
        if (cssText.indexOf("nuance_bg.") < 0)
            return;
    }
    cssText = cssText.replace("nuance_bg.png", "");

    if (container.style.setAttribute)
        container.style.setAttribute('cssText', cssText);
    else
        container.style.cssText = cssText;
}
function NUSAI_addLogoToElement(element, animated) {
    var container = null;
	
	if(!element)
	{
		return;
	}
	
    if (element.attributes && element.attributes[NUSAI_logoContainer]) {
        var containerId = element.attributes[NUSAI_logoContainer].value;
        container = NUSAI_document.getElementById(containerId);
        if (!container)
            alert("Element with id=" + containerId + " not found!\nThis is set as the marker container for element id=" + element.id);
    }

    if (!container)
        container = element;
    else {
        container.visibility = "visible";
        container.style.display = "";
    }

    if (container.tagName == "IMG") {
        var imgSrc;
        if (animated)
            imgSrc = NUSAI_getResourcePath("images/nuance_bg.gif");
        else
            imgSrc = NUSAI_getResourcePath("images/nuance_bg.png");
        container.setAttribute("src", imgSrc);
        return;
    }
    var cssText = container.style['cssText'];
    var newStyle = true;
    if (cssText && cssText.length > 0) {
        if (cssText.indexOf("nuance_bg.")>=0)
            newStyle = false;
        else
            cssText += ";";
    }
    if (newStyle) {
        cssText += "background-image:url(" + NUSAI_getResourcePath("images/nuance_bg.png") + ");background-repeat:no-repeat;background-position:right top;";
    }
    else {
        if (animated)
            cssText = cssText.replace("nuance_bg.png", "nuance_bg.gif");
        else
            cssText = cssText.replace("nuance_bg.gif", "nuance_bg.png");
    }
    if (container.style.setAttribute)
        container.style.setAttribute('cssText', cssText);
    else
        container.style.cssText = cssText;
}

function NUSAI_getNodeAddressChar(idx) {
    if (idx < 10)
        return String.fromCharCode(48 + idx);
    else
        return String.fromCharCode(55 + idx);
}

function NUSAI_onElementFocusableClickInternal(e) {
    NUSAI_onFocusableClicked(e);
}
function NUSAI_onElementFocusInternal(e) {    
    NUSAI_onElementFocus(e);
}
function NUSAI_EventInfo(text) {
    NUSAI_logInfo("NUSAI_EventInfo", text);
}
function NUSAI_EventGeneralError(text) {
    NUSAI_logError("NUSAI_EventGeneralError", text);
}
function NUSAI_getLoggingText(text) {
    while (text.search("\r") >= 0)
        text = text.replace("\r", "#");
    while (text.search("\n") >= 0)
        text = text.replace("\n", "@");
    while (text.search("\f") >= 0)
        text = text.replace("\f", "*");

    return text;

}
function NUSAI_EventReplaceText(requestId, dataKey, newText, selectionStartAfter, selectionLengthAfter, textBefore, selectionStartBefore, selectionLengthBefore) {
    var newTextForLog = NUSAI_getLoggingText(newText);
    var oldTextForLog = NUSAI_getLoggingText(textBefore);

    NUSAI_logInfo("NUSAI_EventReplaceText", requestId + "," + dataKey + ", new:" + newTextForLog + "," + selectionStartAfter + "/" + selectionLengthAfter + ",before:" + oldTextForLog + "," + selectionStartBefore + "/" + selectionLengthBefore);
    var elementId = NUSAI_ids["_" + dataKey];
    if (!elementId)
        elementId = NUSAI_ids[dataKey];
    
    if (!elementId) {
        NUSAI_logWarning("NUSAI_EventReplaceText", "element not found:" + dataKey);
        return;
    }
   
    NUSAI_lastProcessedReplaceTextEvent = requestId;
    NUSAI_replaceTextInternal(requestId, elementId, newText, textBefore, selectionStartBefore, selectionLengthBefore, selectionStartAfter, selectionLengthAfter);
    NUSAI_onValueChanged(elementId, newText);
}

function NUSAI_EventModeChanged(requestId, mode) {
    NUSAI_logInfo("NUSAI_EventModeChanged", requestId + "," + mode); 
}

function NUSAI_EventError(requestId, error) {
    NUSAI_logError("NUSAI_EventError", requestId + " failed",error);
}

function NUSAI_EventValueChanged(requestId, dataKey, value) {
    NUSAI_logInfo("NUSAI_EventValueChanged", requestId + "," + dataKey + "," + value);
    var elementId = NUSAI_ids["_" + dataKey];
    if (!elementId)
        elementId = NUSAI_ids[dataKey];

    if (!elementId) {
        NUSAI_logWarning("NUSAI_EventValueChanged", "element not found:" + dataKey);
        return;
    }

    try {
        NUSAI_document.getElementById(elementId).value = value;
    }
    catch (error) {
        NUSAI_logError("NUSAI_EventValueChanged", "Failed to set value for " + elementId, error);
    }
}

function NUSAI_EventActiveChanged(requestId, dataKey, active){
    NUSAI_logInfo("NUSAI_EventActiveChanged", requestId + "," + dataKey + "," + active);
    var elementId = NUSAI_ids[dataKey];
    if (!elementId) {
        NUSAI_logWarning("NUSAI_EventActiveChanged", "sfId not found:" + dataKey);
        return;
    }
    var element = NUSAI_document.getElementById(elementId);
    if (!element){
        NUSAI_logWarning("NUSAI_EventActiveChanged", "element not found:" + dataKey);
        return;
    }
    if (active)
        NUSAI_addClass(element, NUSAI_classActive);
    else
        NUSAI_removeClass(element, NUSAI_classActive);

    elementId = NUSAI_ids["_" + dataKey];
    element = NUSAI_document.getElementById(elementId);
    if (!element)
        return;

     if (active)
        NUSAI_addClass(element, NUSAI_classActive);
    else
        NUSAI_removeClass(element, NUSAI_classActive);
}
function NUSAI_onRcgBusy() {
    NUSAI_showRcgProcess(true);
}
function NUSAI_EventUtteranceProcessed(requestId) {
    --NUSAI_utterancePending;

    if (NUSAI_utteranceHistory && NUSAI_utteranceHistory.length>0)
        NUSAI_utteranceHistory.shift();

    var info = NUSAI_utteranceQueue.shift();
	
	if (typeof info != 'undefined')
	{
		--info.utterance;
		if (info.utterance == 0) {
			var element = NUSAI_document.getElementById(NUSAI_ids[info.sfId]);
			NUSAI_removeClass(element, NUSAI_classResultPending);
			NUSAI_addLogoToElement(element, false);
		}
	}

    if (NUSAI_utterancePending <= 0) {
        NUSAI_utterancePending = 0;
        NUSAI_showRcgProcess(false);
        if (!NUSAI_isRecording)
        {
            NUSAI_recognitionFinished();
            var lastElement = NUSAI_document.getElementById(NUSAI_lastFocusedSpeechElementId);
            NUSAI_removeClass(lastElement, NUSA_focusedElement);
            NUSAI_addLogoToElement(lastElement, false);
        }
        NUSA_onProcessingFinished();
    }
    NUSAI_logInfo("NUSAI_EventUtteranceProcessed",requestId);
}

function NUSAI_EventFormInitialized(requestId) {
    NUSAI_logInfo("NUSAI_EventFormInitialized", requestId);

    NUSAI_storeFormKPIData();

    NUSAI_onInitialized();
}

function NUSAI_EventFocusChanged(requestId, dataKey){
    NUSAI_logInfo("NUSAI_EventFocusChanged", requestId + "," + dataKey);

    var elementId = NUSAI_ids["_" + dataKey];
    if (!elementId)
        elementId = NUSAI_ids[dataKey];
    if (!elementId) {
        NUSAI_logWarning("NUSAI_EventFocusChanged", "element not found:" + dataKey);
        return;
    }

    if (requestId < NUSAI_lastFocussedRequestId) { // focus could be on non-SF
        NUSAI_logTrace("NUSAI_EventFocusChanged", "old request - ignored:" + requestId + "<" + NUSAI_lastFocussedRequestId);
        if (NUSAI_lastFocussedSpeechFieldRequestId < requestId) { // active SF changed - only mark as focussed
            NUSAI_markNode(elementId);
            NUSAI_lastFocusedSpeechElementId = elementId;
        }              
        return;
    }


    NUSAI_lastDanubeFocusId = dataKey;
    NUSAI_logTrace("NUSAI_EventFocusChanged", "NUSAI_lastDanubeFocusId=" + NUSAI_lastDanubeFocusId);

    NUSAI_selectNode(elementId);
    NUSAI_onFocusChanged(elementId);
}

function NUSAI_EventEnableLogging(requestId, logLevel, moduleLogLevels) {
    NUSAI_logInfo("NUSAI_EventEnableLogging", requestId + "," + logLevel + "," + moduleLogLevels);

    NUSAI_logLevel = NUSAI_logLevel_Info;
    NUSAI_logInfo("System Info", NUSAI_getRuntimeInfo());
    NUSAI_logInfo("System Info", "URL:" + NUSAI_document.URL);
    NUSAI_logInfo("System Info", "service: " + NUSA_service);
    NUSAI_logLevel = logLevel;

    NUSAI_logTrace("NUSAI_setBrowser", "isMSExplorer=" + NUSAI_isMSExplorer + ",isMSExplorer9=" + NUSAI_isMSExplorer9 + ",isMozilla=" + NUSAI_isMozilla + ",isChrome=" + NUSAI_isChrome + ",isSafari=" + NUSAI_isSafari);
}
function NUSAI_EventSelectText(requestId, dataKey, start, length) {
    var elementId = NUSAI_ids["_" + dataKey];
    if (!elementId)
        elementId = NUSAI_ids[dataKey];
    if (!elementId) {
        NUSAI_logWarning("NUSAI_EventSelectText", "element not found:" + dataKey);
        return;
    }

    var element = NUSAI_document.getElementById(elementId);

    NUSAI_setSelection(element, start, length);
    
    if (start + length == 0) {
        element.scrollTop = 0;
        element.scrollLeft = 0;
    }
    else if (start + length == NUSAI_getText(element).length) {
        element.scrollTop = element.scrollHeight;
    }

}

function NUSAI_EventDictationAborted(error) {
//    NUSAI_isRecording = false;
    NUSAI_clearHistory();

    NUSAI_showRcgProcess(false);
    NUSAI_recognitionFinished();
    if (NUSAI_lastFocusedSpeechElementId == null)
        return;
    var lastElement = NUSAI_document.getElementById(NUSAI_lastFocusedSpeechElementId);
    if (lastElement == null)
        return;
    NUSAI_removeClass(lastElement, NUSA_focusedElement);
    NUSAI_addLogoToElement(lastElement, false);
}
function NUSAI_eventVolume(volume, quality) {
    NUSAI_onRecordingLevelChanged(volume, quality);
}
function NUSAI_EventCloseSession() {
    NUSAI_logInfo("NUSAI_EventCloseSession");
    NUSAI_CloseSession();
}
function NUSAI_updateTextBoxes() {
    try {
        NUSAI_logEnter("NUSAI_updateTextBoxes");
        for (var id in NUSAI_ids) {
            var element = NUSAI_document.getElementById(NUSAI_ids[id]);
            if (element != null) {
                if (element.type == "text" || element.type == "textarea" || NUSAI_isContentEditable(element))
                    NUSAI_updateTextBox(element.id);
            }
            else
                NUSAI_logWarning("NUSAI_updateTextBoxes", "element not found:" + NUSAI_ids[id] + "/" + id);
        }
    }
    finally {
        NUSAI_logExit("NUSAI_updateTextBoxes");
    }
}
function NUSAI_updateTextBox(elementId) {
    try {
        NUSAI_logEnter("NUSAI_updateTextBox", elementId);
        var element = NUSAI_document.getElementById(elementId);
        var sfId = element.attributes[NUSAI_Id].value;
        var info = NUSAI_getSpeechFieldInfo(sfId);

        if (info == null) {
            info = new NUSAI_speechFieldInfo();
            info.sfId = sfId;
            info.utterance = 0;
            NUSAI_fieldHistory.push(info);
        }

        var selectionInfo = NUSAI_getSelection(element);
        var selectionStart = selectionInfo.start;
        var selectionLength = selectionInfo.length;
        var text = NUSAI_getText(element);
       
        if (selectionStart != info.selectionStart || selectionLength != info.selectionLength || text != info.text) {
            NUSAI_logInfo("NUSAI_updateTextBox", "text or selection changed in " + elementId);
            info.selectionStart = selectionStart;
            info.selectionLength = selectionLength;
            info.text = text;
            NUSAI_SetTextSurrounding(NUSAI_getSpeechFieldId(info.sfId), text, selectionStart, selectionLength);
        }
    }
    catch (error) {
        NUSAI_logError("NUSAI_updateTextBox", "Failed", error);
    }
    finally {
        NUSAI_logExit("NUSAI_updateTextBox");
    }
}
function NUSAI_setHistoryRequestId(requestId) {
    if (NUSAI_utteranceHistory == null || NUSAI_utteranceHistory.length == 0)
        return;

    NUSAI_logInfo("NUSAI_setHistoryRequestId:" + requestId);

    for (var i = 0; i < NUSAI_utteranceHistory.length; ++i) {
        var id = NUSAI_utteranceHistory[i].requestId;
        if (id < 0) {
            NUSAI_utteranceHistory[i].requestId = requestId;
            break;
        }
    }

    NUSAI_historyRequestIdNeeded = false;
}
function NUSAI_audioDataSent(requestId) {    
    if (NUSAI_historyRequestIdNeeded)
        NUSAI_setHistoryRequestId(requestId);
 }
function NUSAI_newUtteranceStarting(firstUtteranceForForm) {
    try {
        NUSAI_logEnter("NUSAI_newUtteranceStarting - " + firstUtteranceForForm); 

        if (firstUtteranceForForm) {
            NUSAI_lastDanubeFocusId = null;
            NUSAI_clearHistory();
        }

        ++NUSAI_utterancePending;
        NUSAI_showRcgProcess(true);
        var elementId = NUSAI_lastFocusedSpeechElementId;
        var element = NUSAI_document.getElementById(elementId);
        var sfId = element.attributes[NUSAI_Id].value;


        NUSAI_SetFocus(NUSAI_getSpeechFieldId(sfId));

        if (NUSAI_mode == "dictation")
            NUSAI_StartDictation();

        NUSAI_updateTextBoxes();

        var info = NUSAI_getSpeechFieldInfo(sfId);
        ++info.utterance;
        NUSAI_utteranceQueue.push(info);

        NUSAI_historyRequestIdNeeded = true;
        var utteranceInfo = new NUSAI_utteranceInfo();
        utteranceInfo.requestId = -1;
        utteranceInfo.selectionStart = info.selectionStart;
        utteranceInfo.selectionLength = info.selectionLength;
        NUSAI_utteranceHistory.push(utteranceInfo);

        if (info.utterance == 1) {
            NUSAI_addClass(element, NUSAI_classResultPending);
            if (!NUSAI_bubbleContainer)
                NUSAI_addLogoToElement(element, true);
            NUSA_onProcessingStarted();
        }
    }
    finally {
        NUSAI_logExit("NUSAI_newUtteranceStarting");
    }
}
function NUSAI_setReadOnly(element, readonly) {
    //does not work for the IE
    return;

    if (readonly)
        element.setAttribute("readOnly", "readOnly");
    else
        element.removeAttribute("readOnly");
}
function NUSAI_onInfo(info) {
    NUSA_onInfo(info);
}
function NUSAI_onError(text) {
    NUSA_onError();
}
function NUSAI_onSessionOpened(sessionId) {
    NUSAI_sessionId = sessionId;
    NUSAI_storeSessionKPIData();
}
function NUSAI_onSessionOpenFailed(error) {
} 
function NUSAI_onBeforeRecordingStarted() {
    NUSAI_logInfo("NUSAI_onBeforeRecordingStarted");
}
function NUSAI_onRecordingStarted() {
    NUSAI_logInfo("NUSAI_onRecordingStarted");

    NUSAI_isRecording = true;

    if (NUSAI_lastFocusedSpeechElementId == null) {

        for (var id in NUSAI_ids) {
            NUSAI_selectNode(NUSAI_ids[id]);
            break;
        }
    }
	
	if($('#'+NUSAI_lastFocusedSpeechElementId).length)
	{
		var lastElement = NUSAI_document.getElementById(NUSAI_lastFocusedSpeechElementId);
		lastElement.focus();
		NUSAI_addClass(lastElement, NUSA_focusedElement);
		NUSAI_ensureVisible(lastElement, NUSAI_bubbleContainer);
	
		NUSAI_recognitionStarted();  
	
		if (NUSAI_mode == "dictation")
			NUSAI_StartDictation();
	
		NUSA_onRecordingStarted();
	}
}
function NUSAI_onRecordingStopped() {
    NUSAI_logInfo("NUSAI_onRecordingStopped");

    NUSAI_isRecording = false;
    NUSAI_onRecordingLevelChanged(0);

    var lastElement = NUSAI_document.getElementById(NUSAI_lastFocusedSpeechElementId);

    if (NUSAI_utterancePending <= 0)
        NUSAI_recognitionFinished();
    else
        NUSAI_addLogoToElement(lastElement, true);
            
    NUSAI_removeClass(lastElement, NUSA_focusedElement);
    
    NUSAI_onRecordingStoppedInternal();

    NUSA_onRecordingStopped();
}
function NUSAI_OnCDEventReceived(cdEvent) {
    if (cdEvent!=100)      
        return;

    if (NUSAI_isRecording)
        NUSAI_Stop(1);
    else
        NUSAI_Record(1);         
}
function NUSAI_Alert(text) {    
    var msg = "NuanceSpeechAnywhere - Error\n\n" + text;
    alert(msg);
}
function NUSAI_hideInfoWindow() {
    if (NUSAI_infoWindow && !NUSAI_infoWindow.closed)
        NUSAI_infoWindow.close();
}
function NUSAI_showInfoWindow(url) {    
    if (!url || url.length == 0)
        url = NUSAI_getResourcePath("infowindow/infowindow.htm");
    
    NUSAI_logInfo("NUSAI_showInfoWindow - " + url);

    if (!NUSAI_infoWindow || NUSAI_infoWindow.closed) {
        NUSAI_logInfo("NUSAI_showInfoWindow - creating new window");
        var params = "top=0,left=0,scrollbars=1,width=" + NUSAI_infoWindowWidth + ",height=" + NUSAI_infoWindowHeight;
        NUSAI_logInfo("NUSAI_showInfoWindow - params:" + params);
        NUSAI_infoWindow = (window.open(url, NUSAI_infoWindowTitle, params));
        NUSAI_infoWindow.opener = self;
    }
    else {
        NUSAI_logInfo("NUSAI_showInfoWindow - using NUSAI_infoWindow");
        NUSAI_infoWindow.focus();
        NUSAI_infoWindow = window.open(url, NUSAI_infoWindowTitle); // loads the document in NUSAI_infoWindowTitle
        //NUSAI_infoWindow.opener = self;
    }
    NUSAI_logInfo("NUSAI_showInfoWindow - done");
}
function NUSAI_showRcgProcess(show) {
}
function NUSAI_recognitionStarted() {
}
function NUSAI_recognitionFinished() {
}
function NUSAI_onRecordingLevelChanged(level) {
}
function NUSAI_createHtmlGUI() {
}
function NUSAI_createDanubeControl() {
}
function NUSAI_SetTextSurrounding(sfId, text, selectionStart, selectionLength) {
}
function NUSAI_onControlLoaded() {
}
function NUSAI_storeFormKPIData() {
}
function NUSAI_storeSessionKPIData() {
}
function NUSAI_speechElementFocussed() {
}
function NUSAI_addServerLogLine(level, method, text) {
}
function NUSAI_CloseService(closeSession) {
}
function NUSAI_storeCurrentRequestId() {
}
function NUSAI_unloading() { 
}
function NUSAI_IsVuiFormClosed() {
}/// gui.js ////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////////////

var NUSAI_volumeSegment = new Array();
var NUSAI_timer;

function NUSAI_recognitionFinished() {
    if (NUSAI_bubbleContainer)
        NUSAI_bubbleContainer.style.visibility = "hidden";
}
function NUSAI_recognitionStarted() {
    if (!NUSAI_bubbleContainer)
        return;

    var button = NUSAI_document.getElementById("NUSAI_Bubble_Button");
    if (!button)
        return;
    button.className = "NUSAI_Bubble_Button_On";

    NUSAI_bubbleContainer.style.visibility = "visible";

    NUSAI_timer = setTimeout("NUSAI_speechElementFocussed()", 200);

}
function NUSAI_onRecordingStoppedInternal() {
    var button = NUSAI_document.getElementById("NUSAI_Bubble_Button");
    if (!button)
        return;

    button.className = "NUSAI_Bubble_Button_Off";

    if (NUSAI_bubbleContainer)
        NUSAI_bubbleContainer.style.visibility = "hidden";

    if (NUSAI_timer)
        clearTimeout(NUSAI_timer);

}
function NUSAI_speechElementFocussed() {
    if (!NUSAI_bubbleContainer)
        return;

    var element = NUSAI_document.getElementById(NUSAI_lastFocusedSpeechElementId);
    if (!element)
        return;

    var selectedPosX = 0;
    var selectedPosY = element.offsetHeight;
    while (element != null) {
        selectedPosX += element.offsetLeft;
        selectedPosY += element.offsetTop;
        element = element.offsetParent;
    }
    var y = selectedPosY;
    var x = selectedPosX;

    NUSAI_bubbleContainer.style.top = y + "px";
    NUSAI_bubbleContainer.style.left = x + "px";  

    if (NUSAI_bubbleContainer.style.visibility == "visible")
        NUSAI_timer = setTimeout("NUSAI_speechElementFocussed()", 200);
}
function NUSAI_showRcgProcess(show) {
    var element = NUSAI_document.getElementById("NUSAI_Bubble_Flame");
    if (!element)
        return;

    if (show) {
        NUSAI_removeClass(element, "NUSAI_Bubble_Flame_Idle");
        NUSAI_addClass(element, "NUSAI_Bubble_Flame_Busy");
    }
    else {
        NUSAI_removeClass(element, "NUSAI_Bubble_Flame_Busy");
        NUSAI_addClass(element, "NUSAI_Bubble_Flame_Idle");
    }
}


function NUSAI_onRecordingLevelChanged(level, quality) {
    if (NUSAI_volumeSegment.length < 10)
        return;

    var volume = 0.10 * level;

    var qualityClass = "NUSAI_Bubble_VolumeSegment_Green";
    if (quality == 1) // too noisy
        qualityClass = "NUSAI_Bubble_VolumeSegment_Red";
    if (quality == 2) // too soft
        qualityClass = "NUSAI_Bubble_VolumeSegment_Yellow";
    if (quality == 3) // too loud
        qualityClass = "NUSAI_Bubble_VolumeSegment_Red";

    for (var i = 0; i < 10; ++i) {
        if (i < volume) 
            NUSAI_volumeSegment[i].className =  qualityClass;
        else 
            NUSAI_volumeSegment[i].className = "NUSAI_Bubble_VolumeSegment_Disabled";
    }


}
function NUSAI_onBubbleButtonClick() {
    var button = NUSAI_document.getElementById("NUSAI_Bubble_Button");

    if (NUSAI_isRecording) 
        NUSAI_Stop(0);
    else
        NUSAI_Record(0);
            
    return false;
}

function NUSAI_createBubbleGUI() {
    var parent = NUSAI_document.getElementsByTagName("body")[0];

    //main container
    NUSAI_bubbleContainer = NUSAI_document.createElement("div");
    NUSAI_bubbleContainer.id = "NUSAI_Bubble_Container";
    NUSAI_bubbleContainer.style.visibility = "hidden";
    NUSAI_bubbleContainer.style.opacity = 0.75;
    NUSAI_bubbleContainer.style.zIndex = "9000";

    var bubble = NUSAI_document.createElement("div");
    bubble.id = "NUSAI_Bubble";
    bubble.style.position = "relative";
    bubble.style.top = "12px";
    bubble.style.left = 0;
    
        // Arrow
        var arrow = NUSAI_document.createElement("div");
        arrow.id = "NUSAI_Bubble_Arrow_Top_Left";
        bubble.appendChild(arrow);

        // Record button
        var button = NUSAI_document.createElement("div");
        button.id = "NUSAI_Bubble_Button";
        var buttonOnClass = NUSAI_document.createAttribute("class");
        buttonOnClass.nodeValue = "NUSAI_Bubble_Button_Off";
        button.setAttributeNode(buttonOnClass);
        button.onclick = function () { NUSAI_onBubbleButtonClick(); };
        bubble.appendChild(button);


        var volume = NUSAI_document.createElement("div");
        volume.id = "NUSAI_Bubble_Theme_Volumemeter";
        for (var i = 0; i < 10; ++i) {
            NUSAI_volumeSegment.push(NUSAI_document.createElement("div"));
            NUSAI_volumeSegment[i].id = "NUSAI_Bubble_VolumeSegment_" + (i + 1);
            var segmentClass = NUSAI_document.createAttribute("class");
            segmentClass.nodeValue = "NUSAI_Bubble_VolumeSegment_Disabled";
            NUSAI_volumeSegment[i].setAttributeNode(segmentClass);
            volume.appendChild(NUSAI_volumeSegment[i]);
        }
        bubble.appendChild(volume);


        // Flame
        var flame = NUSAI_document.createElement("div");
        flame.id = "NUSAI_Bubble_Flame";
        var flameClass = NUSAI_document.createAttribute("class");
        flameClass.nodeValue = "NUSAI_Bubble_Flame_Idle";
        flame.setAttributeNode(flameClass);
        bubble.appendChild(flame);
    
    NUSAI_bubbleContainer.appendChild(bubble);

    if (parent.firstChild)
        parent.insertBefore(NUSAI_bubbleContainer, parent.firstChild);
    else
        parent.appendChild(NUSAI_bubbleContainer);

    NUSAI_onRecordingLevelChanged(0);

}
/// silverlight.js /////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////

NUSAI_slControlVersion = "version not set (yet)";

var NUSAI_slControl;
var NUSAI_lastDanubeFocusId;

function NUSAI_getFilterSets() {
    var filter = "";
    if (NUSAI_filterList && NUSAI_filterList.length > 0) {
        for (var i = 0; i < NUSAI_filterList.length; ++i) {
            if (i > 0)
                filter += ";";
            filter += NUSAI_filterList[i].key + "=" + NUSAI_filterList[i].value;
        }
    }
    NUSAI_logTrace("NUSAI_getFilterSets:" + filter);
    return filter;        
}
function NUSAI_createDanubeControl() {
    NUSAI_logInfo("NUSAI_createDanubeControl");

    var bodyElement = NUSAI_document.getElementsByTagName("body")[0];

    var parent = null;
    if (NUSA_container.length > 0)
        parent = NUSAI_document.getElementById(NUSA_container);

    if (!parent)
        parent = bodyElement;

    var h = 0;
    var w = "100%";
    if (NUSAI_uiMode == NUSAI_uiMode_Full)
        h = NUSAI_debug ? "500px" : "200px";
    else if (NUSAI_uiMode == NUSAI_uiMode_Minimal)
        h = NUSAI_debug ? "350px" : "44px";
    else {
        h = NUSAI_debug ? "340px" : NUSA_recordButtonHeight;
        w = NUSA_recordButtonWidth;
    }

    var container = NUSAI_document.createElement("div");
    container.id = "NUSA_containerId";

    try{
        var style = NUSAI_document.createAttribute("style");
        style.nodeValue = "height: "+h+";";
        container.setAttributeNode(style);
    } catch(e) {}

    try{
        var bodyStyle = bodyElement.attributes["style"];
        if (!bodyStyle) {
            bodyStyle = NUSAI_document.createAttribute("style");
            bodyStyle.nodeValue = "height: " + h + ";";// "height: 100%;";
            bodyElement.setAttributeNode(bodyStyle);
        }
        else {
            bodyStyle.nodeValue += " height: " + h + ";"; //" height: 100%;";
            bodyElement.setAttributeNode(bodyStyle);
        }
    } catch(e){}     
       
    if (parent.firstChild)
        parent.insertBefore(container, parent.firstChild);
    else
        parent.appendChild(container);


   
    var controlSource = NUSA_resourceUrl + "ClientBin/Nuance.SpeechAnywhere.xap";

    if (NUSAI_document.URL.indexOf("https") < 0) // document is http
        controlSource = controlSource.replace("https", "http"); // make sure control is loaded via http
    else if (controlSource.indexOf("https") < 0) // document is https, but control is http
        controlSource = controlSource.replace("http", "https");

    var params = "";
    if (NUSA_recordButtonImage_RecordOn && NUSA_recordButtonImage_RecordOnHover && NUSA_recordButtonImage_RecordOff && NUSA_recordButtonImage_RecordOffHover)
        params = "micOn=" + NUSA_recordButtonImage_RecordOn + ", micOn_Over=" + NUSA_recordButtonImage_RecordOnHover +
        ", micOff=" + NUSA_recordButtonImage_RecordOff + ", micOff_Over=" + NUSA_recordButtonImage_RecordOffHover +
        ", micDisabled=" + NUSA_recordButtonImage_RecordDisabled + ", micDisabled_Over=" + NUSA_recordButtonImage_RecordDisabledHover;
    
    try {
        Silverlight.createObjectEx(
        {
            source: controlSource, 
            parentElement: container,
            id: "DanubeControl",
            properties: { width: w, height: h, version: "4.0.41108.0", background: NUSA_recordButtonBackgroundColor, enableHtmlAccess: "true", initParams: params},
            events: { onLoad: NUSAI_onDanubeControlLoad }            
        }
        );
    }
    catch (error) {
        NUSAI_logError("NUSAI_createDanubeControl","createObjectEx failed", error);
        return;
    }
}
function NUSAI_isSLControlInitialized() {
    return NUSAI_slControl && NUSAI_slControl.Content;
}
function NUSAI_onControlLoaded() {
    NUSAI_logInfo("NUSAI_onControlLoaded");
}
function NUSAI_onDanubeControlLoad(control, userContext, sender) {

    NUSAI_logInfo("NUSAI_onDanubeControlLoad");
    NUSAI_slControl = control;
    NUSAI_addEventListenerFocus(control);

    try {
        NUSAI_slControlVersion = NUSAI_slControl.Content.Danube.Version;
        NUSAI_slControl.Content.Danube.ClientVersion = NUSAI_version;
    }
    catch (error) { }    

    NUSAI_slControl.Content.Danube.UserId = NUSA_userId;
    NUSAI_slControl.Content.Danube.Topic = NUSA_topic;
    NUSAI_slControl.Content.Danube.LanguageId = NUSA_language;

    NUSAI_slControl.Content.Danube.Service = NUSA_service;
    NUSAI_slControl.Content.Danube.PartnerToken = NUSAI_partnerGuid;
    NUSAI_slControl.Content.Danube.ApplicationName = NUSA_applicationName;
    NUSAI_slControl.Content.Danube.AccountToken = NUSAI_licenseGuid;
    NUSAI_slControl.Content.Danube.AudioFormat = NUSAI_audioFormat;
    NUSAI_slControl.Content.Danube.ProtocolVersion = NUSAI_protocolVersion;
    NUSAI_slControl.Content.Danube.NodeString = NUSAI_nodeList;
    NUSAI_slControl.Content.Danube.FilterSets = NUSAI_getFilterSets();
    NUSAI_slControl.Content.Danube.UI = NUSAI_uiMode;
    NUSAI_slControl.Content.Danube.InputChannel = "DesktopMic";
    NUSAI_slControl.Content.Danube.DebugMode = NUSAI_debug;
    NUSAI_slControl.Content.Danube.OnPremise = NUSAI_onPremise;

    if (NUSAI_logDataTreshold > 0)
        NUSAI_slControl.Content.Danube.LogDataTreshold = NUSAI_logDataTreshold;

    if (NUSAI_initializeOnLoad) {
        NUSAI_slControl.Content.Danube.InitService(NUSAI_sessionId, NUSAI_initialRequestId);
        if (NUSAI_onPremise && !NUSAI_sessionId)
            NUSAI_slControl.Content.Danube.OpenSession();
    }
}
function NUSAI_GetCurrentRequestId() {
    if (!NUSAI_isSLControlInitialized())
        return "";
    return NUSAI_slControl.Content.Danube.CurrentRequestId;
}
function NUSAI_CloseService(closeSession) {
    if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_slControl.Content.Danube.CloseService(closeSession);
}
function NUSAI_CloseSession() {
    if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_slControl.Content.Danube.CloseSession();
}
function NUSAI_CloseVuiForm() {
    if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_slControl.Content.Danube.CloseVuiForm();
}

function NUSAI_Record(source) {
    if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_slControl.Content.Danube.Record(source);
}
function NUSAI_Stop(source) {
    if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_slControl.Content.Danube.Stop(source);
}
function NUSAI_StartDictation() {
    if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_logInfo("NUSAI_StartDictation");
    NUSAI_slControl.Content.Danube.StartDictation();
}
function NUSAI_StartNavigation() {
    if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_logInfo("NUSAI_StartNavigation");
    NUSAI_slControl.Content.Danube.StartNavigation();
}
function NUSAI_SetFocus(sfId) {
    if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_logInfo("NUSAI_SetFocus", sfId);
    try {
        if (NUSAI_lastDanubeFocusId == sfId) {
            NUSAI_logTrace("NUSAI_SetFocus", "ignored: NUSAI_lastDanubeFocusId=" + NUSAI_lastDanubeFocusId);
            return;
        }
        NUSAI_slControl.Content.Danube.SetFocus(sfId);
        NUSAI_lastDanubeFocusId = sfId;
        NUSAI_lastFocussedSpeechFieldRequestId = NUSAI_slControl.Content.Danube.CurrentRequestId;
    }
    catch (error) {       
    }
}
function NUSAI_ForceFlush() {
    if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_logInfo("NUSAI_ForceFlush");
    try {
        NUSAI_slControl.Content.Danube.ForceFlush();
    }
    catch (error) {
    }
}

function NUSAI_SetTextSurrounding(sfId, text, selectionStart, selectionLength) {
    if (!NUSAI_isSLControlInitialized())
        return;
    var textForLog = NUSAI_getLoggingText(text);
    NUSAI_logInfo("NUSAI_SetTextSurrounding", sfId + "," + textForLog + "," + selectionStart + "/" + selectionLength);
    if (NUSAI_slControl && NUSAI_slControl.Content)
        NUSAI_slControl.Content.Danube.SetTextSurrounding(sfId, text, selectionStart, selectionLength, NUSAI_lastProcessedReplaceTextEvent);
}

function NUSAI_GetVersion() {
}

function NUSAI_storeFormKPIData() {
    if (!NUSAI_isSLControlInitialized())
        return;

    try {        
        NUSAI_slControl.Content.Danube.SetKPIVuiFormData(NUSAI_controlCount);
    }
    catch (error) {
        NUSAI_logError("NUSAI_storeFormKPIData", "failed:", error);
    }
}
function NUSAI_storeSessionKPIData() {
    if (!NUSAI_isSLControlInitialized())
        return;

    try {
        var runtimeInfo = NUSAI_getRuntimeInfo();
        var date = new Date();
        var timezone = -date.getTimezoneOffset()/60;
        NUSAI_slControl.Content.Danube.SetKPISessionData("browser", runtimeInfo, timezone);
    }
    catch (error) {
        NUSAI_logError("NUSAI_storeSessionKPIData", "failed:", error);
    }
}

function NUSAI_addServerLogLine(level, method, text) {
    if (!NUSAI_isSLControlInitialized())
        return;

    try {
        NUSAI_slControl.Content.Danube.AddLogLine(level, method, text);
    }
    catch (error) { }
}
function NUSAI_storeCurrentRequestId() {
    if (!NUSAI_isSLControlInitialized())
        return;

    try {
        NUSAI_lastFocussedRequestId = NUSAI_slControl.Content.Danube.CurrentRequestId;
        NUSAI_logTrace("NUSAI_storeCurrentRequestId", "NUSAI_lastFocussedRequestId=" + NUSAI_lastFocussedRequestId);
    }
    catch (error) { }
}
function NUSAI_nodeListChanged() {
    if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_slControl.Content.Danube.NodeString = NUSAI_nodeList;
    NUSAI_slControl.Content.Danube.FilterSets = NUSAI_getFilterSets();
    NUSAI_slControl.Content.Danube.ResetVuiForm();
}
function NUSAI_unloading() {
 if (!NUSAI_isSLControlInitialized())
        return;

    NUSAI_slControl.Content.Danube.ShowMessageBoxes = false;
}
function NUSAI_IsVuiFormClosed() {
    if (!NUSAI_isSLControlInitialized())
        return true;
    return NUSAI_slControl.Content.Danube.IsVuiFormClosed();
}/// text.explorer9.js //////////////////////////////
/////////////////////////////////////////////////////// text.explorer.js ///////////////////////////////
////////////////////////////////////////////////////

function NUSAI_getNewlinesToPosIE(element, position, absolute) {
    NUSAI_logTrace("NUSAI_getNewlinesToPosIE - position:", position);
    var text = NUSAI_getTextIE(element);
    var newline = 0;
    //var isCE = NUSAI_isContentEditable(element);
    for (var i = 0; i < position; ++i) {
        if (i >= text.length)
            break;
        if (text.charAt(i) == '\r') { // the following \n is ignored
            ++newline;
            if (!absolute)
                ++position;
        }
    }
    NUSAI_logTrace("NUSAI_getNewlinesToPosIE - newlines:", newline);
    return newline;
}
function NUSAI_getLineBreakTextIE() {
    return "\r\n";
}
function NUSAI_getContainerBreakTextIE() {
    return "\t\t";
}
function NUSAI_getParagraphTextIE() {
    return "\r\n\r\n";// "\r\f";
}
function NUSAI_getLineBreaksForNodeIE(node) {
    if (node.nodeName == "BR")
        return 2;

    return 0;
}
function NUSAI_nodeNeedsLineBreakIE(node) {
    var type = node.nodeName;
    if (type == "DIV" || type == "P" || type.match("H."))
        return 2;

    return 0;
}
function NUSAI_insertTextIE(element, previousNode, text) {
    var nodes = NUSAI_getNodesFromText(text);
    for (var i = 0; i < nodes.length; ++i) {
        var insertNode = nodes[i];
        NUSAI_insertNodeAfter(element, insertNode , previousNode);
        previousNode = insertNode;
    }
}
/// getText ////////////////////////////////////////
function NUSAI_getTextIE(element) {
    if (NUSAI_isContentEditable(element))
        return NUSAI_getContentEditableText(element, element, "");

    return element.value;
}

/// setText ////////////////////////////////////////
function NUSAI_replaceTextIE(element, text, fromPos, toPos) {
    if (NUSAI_isContentEditable(element))
        NUSAI_replaceContentEditableTextIE(element, text, fromPos, toPos);
    else {
        var elementText = NUSAI_getTextIE(element);
        if (elementText.length > 0)
            elementText = NUSAI_stringRemove(elementText, fromPos, toPos);
        elementText = NUSAI_stringInsert(elementText, fromPos, text);
        element.value = elementText;
    }
}
function NUSAI_replaceContentEditableTextIE(element, text, fromPos, toPos) {
    if (fromPos != toPos) {
        if (NUSAI_lastFocusedElement == element) {// element is focussed            
                NUSAI_setContentEditableSelectionIE(element, fromPos, Number(toPos) - Number(fromPos));
                NUSAI_document.execCommand('delete', false, null);
        }
        else
            NUSAI_deleteNodes(element, fromPos, toPos);
    }

    if (!text || text.length == 0)
        return;

    var rc = NUSAI_getNodeInfo(element, fromPos);
    var node = rc.node;
    if (!node) {
        NUSAI_logTrace("NUSAI_replaceContentEditableTextIE", "node is null");
        return;
    }
    NUSAI_logTrace("NUSAI_replaceContentEditableTextIE", "insert into node :" + node.nodeName);

    if (node == element) {
        var dummy = NUSAI_document.createElement("P");
        node.appendChild(dummy);
        node = dummy;
        NUSAI_logTrace("NUSAI_replaceContentEditableTextIE", "inserting dummy <p>");
    }

    if (node.nodeType == 3) {
        fromPos -= rc.start;
        NUSAI_splitTextNode(node, fromPos);
    }
    NUSAI_insertTextIE(element, node, text);
}

/// getSelection ///////////////////////////////////
function NUSAI_getSelectionIE(element) {
    if (NUSAI_isContentEditable(element))
        return NUSAI_getContentEditableSelectionIE(element);

    var info = new NUSAI_selectionInfo();
    info.start = NUSAI_getSelectionStartIE(element);
    info.length = NUSAI_getSelectionLengthIE(element);
    return info;
}
function NUSAI_getContentEditableSelectionIE(element) {
    var info = new NUSAI_selectionInfo();
    info.start = NUSAI_getContentEditableSelectionStartIE(element);
    info.length = NUSAI_getContentEditableSelectionLengthIE(element);
    return info;
}
/// getSelectionText ///////////////////////////////
function NUSAI_getSelectionTextIE(element) {
    var startPosition = 0;
    var range = NUSAI_document.selection.createRange();
    return range.text;
}
function NUSAI_getContentEditableSelectionTextIE(element) {
    return NUSAI_getSelectionTextIE(element);
}

/// getSelectionStart //////////////////////////////
function NUSAI_getSelectionStartIE(element) {
    var startPosition = 0;
    var range = NUSAI_document.selection.createRange();
    if (element.type == 'text') {
        var isCollapsed = range.compareEndPoints("StartToEnd", range) == 0;
        if (!isCollapsed)
            range.collapse(true);
        var b = range.getBookmark();
        return b.charCodeAt(2) - 2;
    }
    else {
        var dpl = range.duplicate();
        if (range.text.length > 0) {
            try {
                dpl.moveToElementText(element);
            }
            catch (error) {
                NUSAI_logError("NUSAI_getSelectionStart()", "moveToElementText failed:" + error);
            }
            dpl.setEndPoint("EndToEnd", range);
            startPosition = dpl.text.length - range.text.length;
        }
        else
            return NUSAI_getCaretPositionIE(element);
    }
    return startPosition;
}
function NUSAI_getContentEditableSelectionStartIE(element) {
   return NUSAI_getSelectionStartIE(element);
}

/// getSelectionLength /////////////////////////////
function NUSAI_getSelectionLengthIE(element) {
    var range = NUSAI_document.selection.createRange();
    return range.text.length;
}
function NUSAI_getContentEditableSelectionLengthIE(element) {
    return NUSAI_getSelectionLengthIE(element);
}

/// setSelection ///////////////////////////////////
function NUSAI_setSelectionIE(element, start, length) {
    if (NUSAI_isContentEditable(element))
        NUSAI_setContentEditableSelectionIE(element, start, length);
    else {
        var endPos = Number(start) + Number(length);
        start = Number(start) - NUSAI_getNewlinesToPosIE(element, start, true);
        endPos = Number(endPos) - NUSAI_getNewlinesToPosIE(element, endPos, true);

        var range = element.createTextRange();
        range.collapse(true);
        range.moveStart('character', start);
        range.moveEnd('character', endPos-start);
        range.select();
    }
}
function NUSAI_setContentEditableSelectionIE(element, start, length) {
    var setStart = false;
    var oldStart = 0;
    var elementText = NUSAI_getTextIE(element);
    if (NUSAI_lastFocusedElement == element) { // don't call setSelStart twice when focussed - this would mess up the selection
        oldStart = NUSAI_getContentEditableSelectionStartIE(element);        
        if (oldStart > elementText.length)
            oldStart = elementText.length;
        NUSAI_logTrace('NUSAI_setContentEditableSelectionIE', "oldStart=" + oldStart);
        setStart = (oldStart != start);
    }

    var endPos = Number(start) + Number(length);
    endPos = Number(endPos) - NUSAI_getNewlinesToPosIE(element, endPos, true);
    start = Number(start) - NUSAI_getNewlinesToPosIE(element, start, true);

    var range = NUSAI_document.selection.createRange();
    range.collapse(true);

 
    if (setStart) {
        oldStart = Number(oldStart) - NUSAI_getNewlinesToPosIE(element, oldStart, true);
        range.moveStart('character', start - oldStart);
        range.moveEnd('character', 0);
        range.select();
    }
//    var dpl = range.duplicate();
//    try {
//        dpl.moveToElementText(element);
//    }
//    catch (error) { }
    range.collapse(true);
    range.moveEnd('character', endPos-start);
    range.select();    
}

/// getCaretPosition ///////////////////////////////
function NUSAI_getCaretPositionIE(element) {
    NUSAI_logTrace('NUSAI_getCaretPositionIE', element.id);

    if (NUSAI_lastFocusedElement != element)
        return NUSAI_getTextIE(element).length;

    if (NUSAI_isContentEditable(element)) 
        return NUSAI_getContentEditableCaretPositionIE(element);

    var caretPos = 0;

    var sel = NUSAI_document.selection.createRange();
    var sel2 = sel.duplicate();
    try {
        sel2.moveToElementText(element);
    }
    catch (error) { }

    caretPos = -1;
    while (sel2.inRange(sel)) {
        sel2.moveStart('character');
        caretPos++;
    }

    return caretPos + NUSAI_getNewlinesToPosIE(element, caretPos);
}
function NUSAI_getContentEditableCaretPositionIE(element) {
    var range = NUSAI_document.selection.createRange();
    range.collapse(true);
    range.text = " ";
    range.moveStart('character', -1);
    range.select();

    if (range.text.length == 0)
        return NUSAI_getTextIE(element).length;

    var startPosition = 0;
    var range2 = NUSAI_document.selection.createRange();
    var dpl = range2.duplicate();
    if (range2.text.length > 0) {
        try {
            dpl.moveToElementText(element);
        }
        catch (error) {
            NUSAI_logError("NUSAI_getCaretPositionIE()", "moveToElementText failed:" + error);
        }
        dpl.setEndPoint("EndToEnd", range2);
        startPosition = dpl.text.length - range2.text.length;
    }
    range.text = "";
    range.select();
    return startPosition;
}/// text.safari.js /////////////////////////////////
/////////////////////////////////////////////////////// text.chrome.js /////////////////////////////////
/////////////////////////////////////////////////////// text.mozilla.js ////////////////////////////////
////////////////////////////////////////////////////
function NUSAI_getLineBreakTextMozilla() {
    return "\n";
}
function NUSAI_getContainerBreakTextMozilla() {
    return "\t";
}
function  NUSAI_getParagraphTextMozilla() {
    return "\n\n";//"\f"
}
function NUSAI_isValidBR(element, node) {
    if (!node || node.nodeName != "BR")
        return false;
    return NSUAI_getTrailingBreak(element) != node;
}
function NUSAI_getLineBreaksForNodeMozilla(element, node) {
    if (NUSAI_isValidBR(element, node)) 
        return 1;

    return 0;
}
function NUSAI_nodeNeedsLineBreakMozilla(node) {
    var type = node.nodeName;
    if (type == "DIV" || type == "P" || type.match("H."))
        return 1;

    return 0;
}

function NUSAI_insertTextMozilla(element, previousNode, text) {
    var nodes = NUSAI_getNodesFromText(text);
    if (!nodes || nodes.length == 0)
        return;


    var trailingBreak;

    if (element == previousNode && !NUSAI_isContainerElement(nodes[0])) {
        trailingBreak = NSUAI_getTrailingBreak(element);
        if (!trailingBreak) {
            trailingBreak = NUSAI_document.createElement("BR");
            element.appendChild(trailingBreak);
        }
    }

    NUSAI_logTrace("NUSAI_insertTextMozilla", "inserting " + nodes.length + " nodes");
    for (var i = 0; i < nodes.length; ++i) {
        var insertNode = nodes[i];
       
        if (trailingBreak) {
            NUSAI_insertNodeBefore(element, trailingBreak, insertNode);
            trailingBreak = null;
            NUSAI_logTrace("NUSAI_insertTextMozilla", "trailing break found in empty element - inserting node before");
        }
        else {
            NUSAI_insertNodeAfter(element, insertNode, previousNode);
        }

        previousNode = insertNode;
    }
}
/// getText ////////////////////////////////////////
function NUSAI_getTextMozilla(element) {
    if (NUSAI_isContentEditable(element))
        return NUSAI_getContentEditableText(element, element, "");
    
    return element.value;
}

function NUSAI_getContentEditableTextBeforeInternalMozilla(root, node, target, rc) {
    if (!rc) {
        rc = new NUSAI_loopReturnValue();
        rc.value = "";
        rc.stop = false;
    }

    if (!node || node == target) {
        rc.stop = true;
        return rc;
    }


    if (node.nodeType == 3 && node.nodeValue.length > 0) {
        rc.value += node.nodeValue;
    }
    else if (NUSAI_getLineBreaksForNodeMozilla(root,node)) {
        rc.value += NUSAI_getLineBreakTextMozilla();
    }

    if (node.childNodes && node.childNodes.length > 0) {
        var tmpRc = rc;
        for (var j = 0; j < node.childNodes.length; ++j) {
            if (node.childNodes[j] == target) {
                rc.stop = true;
            }
            tmpRc = NUSAI_getContentEditableTextBeforeInternalMozilla(root, node.childNodes[j], target, rc);
            if (!tmpRc)
                return rc;
            rc.value = tmpRc.value;
            rc.stop = tmpRc.stop;
            if (rc.stop)
                return rc;
        }
    }

    if (root != node) {
        var nextNode = node.nextSibling;
        if (nextNode) {
            var isNextNodeEmptyText = nextNode.nodeType == 3 && (!nextNode.nodeValue || nextNode.nodeValue == "");
            var isNodeEmptyText = node.nodeType == 3 && (!node.nodeValue || node.nodeValue == "");
            if (NUSAI_nodeNeedsLineBreak(node)) {
                if (!isNextNodeEmptyText)
                    rc.value += NUSAI_getLineBreakTextMozilla();
            }
            else if (!NUSAI_getLineBreaksForNodeMozilla(root, node) && !isNodeEmptyText) {
                if (NUSAI_nodeNeedsLineBreak(nextNode))
                    rc.value += NUSAI_getLineBreakTextMozilla();
            }
        }
    }
    return rc;
}
/// setText ////////////////////////////////////////
function NUSAI_replaceTextMozilla(element, text, fromPos, toPos) {
    if (NUSAI_isContentEditable(element))
        NUSAI_replaceContentEditableTextMozilla(element, text, fromPos, toPos);
    else {
        var elementText = NUSAI_getTextMozilla(element);
        if (elementText.length > 0)
            elementText = NUSAI_stringRemove(elementText, fromPos, toPos);
        elementText = NUSAI_stringInsert(elementText, fromPos, text);
        element.value = elementText;
    }
}

function NUSAI_replaceContentEditableTextMozilla(element, text, fromPos, toPos) {
    if (fromPos != toPos) 
        NUSAI_deleteNodes(element, fromPos, toPos);

    if (!text || text.length == 0)
        return;

    var rc = NUSAI_getNodeInfo(element, fromPos);
    var node = rc.node;
    if (!node) {
        NUSAI_logTrace("NUSAI_replaceContentEditableTextMozilla", "node is null");
        return;
    }
    NUSAI_logTrace("NUSAI_replaceContentEditableTextMozilla", "insert into node :" + node.nodeName + " id=" + node.id);
    if (node.nodeType == 3) {
        fromPos -= rc.start;
        NUSAI_splitTextNode(node, fromPos);
    }

    NUSAI_insertTextMozilla(element, node, text);
}

/// getSelection ///////////////////////////////////
function NUSAI_getSelectionMozilla(element) {
    if (NUSAI_isContentEditable(element))
        return NUSAI_getContentEditableSelectionMozilla(element);

    var info = new NUSAI_selectionInfo();
    info.start = element.selectionStart;
    info.length = element.selectionEnd - element.selectionStart;
    return info;
}
function NUSAI_getContentEditableSelectionMozilla(element) {          
    var info = new NUSAI_selectionInfo();
    var selStart = -1;
    var selLength = -1;
    var selection = window.getSelection();
    var anchorNode = selection.anchorNode;
    var anchorOffset = selection.anchorOffset;
    if (NUSAI_isContainerElement(anchorNode)) {
        anchorNode = anchorNode.childNodes[anchorOffset];
        anchorOffset = 0;
    }

    var rcBefore = NUSAI_getNodeInfo(element, 0, anchorNode);
    var anchorStartPos = rcBefore.textBefore.length;
    anchorStartPos += anchorOffset;
    selStart = anchorStartPos;

    var focusNode = selection.focusNode;
    var focusOffset = selection.focusOffset;
    if (NUSAI_isContainerElement(focusNode)) {
        focusNode = focusNode.childNodes[focusOffset];
        focusOffset = 0;
    }
    rcBefore = NUSAI_getNodeInfo(element, 0, focusNode);
    var focusStartPos = rcBefore.textBefore.length;
    focusStartPos += focusOffset;

    if (selStart > focusStartPos) {
        info.start = focusStartPos;
        info.length = selStart - focusStartPos;
    }
    else {
        info.start = selStart;
        info.length = focusStartPos - selStart;
    }
    return info;
}
/// getSelectionText ///////////////////////////////
function NUSAI_getSelectionTextMozilla(element) {
    if (NUSAI_isContentEditable(element))
        return NUSAI_getContentEditableSelectionTextMozilla();

    return NUSAI_getTextMozilla(element).substring(element.selectionStart, element.selectionEnd);
}
function NUSAI_getContentEditableSelectionTextMozilla(element) {
    var text = window.getSelection().toString();
    while (text.search("\r") >= 0)
        text = text.replace("\r", "");
    return text;
}

/// setSelection ///////////////////////////////////
function NUSAI_setSelectionMozilla(element, start, length) {
    if (NUSAI_isContentEditable(element))
        NUSAI_setContentEditableSelectionMozilla(element, start, length);
    else {
        element.selectionStart = start;
        element.selectionEnd = Number(start) + Number(length);
    }
}
function NUSAI_setContentEditableSelectionMozilla(element, start, length) {
    var selection = window.getSelection();
    selection.removeAllRanges();
    var range = NUSAI_document.createRange();

    var startRc = NUSAI_getNodeInfo(element, start);

    var startPos = start;
    startPos = Number(startPos) - Number(startRc.start);

    NUSAI_logTrace("NUSAI_setContentEditableSelectionMozilla", "setStart: startNode:" + startRc.node.nodeName + ", startPos:"+startPos );
    if (startRc.node.nodeName == "BR" && startPos > 0) 
        range.setStartAfter(startRc.node);
    else
        range.setStart(startRc.node, startPos);

    if (length == 0) {
        if (startRc.node.nodeName == "BR" && startPos > 0)
            range.setEndAfter(startRc.node);
        else
            range.setEnd(startRc.node, startPos);
    }
    else {
        var endPos = Number(start) + Number(length);
        var endRc = NUSAI_getNodeInfo(element, endPos);
        endPos = Number(endPos) - Number(endRc.start);
        range.setEnd(endRc.node, endPos);
        selection.addRange(range);
    }
    range.collapse(false);

    selection.addRange(range);
}


/// text.common.js //////////////////////////////////////
//////////////////////////////////////////////////

var NUSAI_nodeInfoEx = function () {
    this.node;
    this.start;
    this.end;
    this.textBefore;
    this.nodeFound;
}
function NUSAI_getEventTarget(e) {
    if (!e)
        e = window.event;

    var target;
    if (e.target)
        target = e.target;
    else if (e.srcElement)
        target = e.srcElement;

    if (target.nodeType == 3) // Safari bug
        target = target.parentNode;

    return target;
}
function NUSAI_replaceAll(text, oldString, newString) {
    while (text.search(oldString) >= 0)
        text = text.replace(oldString, newString);
    return text;
}
function NUSAI_removeSourceBlanks(text) {
    var temp = text.split("\n");
    var clearText = "";

    for (var i = 0; i < temp.length; ++i) {
        var index = 0;
        while (index < temp[i].length && temp[i][index] == " ")
            ++index;
        if (index <= temp[i].length)
            clearText += temp[i].substr(index);

    }
    return clearText;
}

function NUSAI_textEndsWithLineBreak(text) {
    if (!text || text.length == 0)
        return false;

    return text.charAt(text.length - 1) == "\n";
}
function NUSAI_textEndsWithContainerBreak(text) {
    if (!text || text.length == 0)
        return false;

    return text.charAt(text.length - 1) == "\t";
}
function NUSAI_stringRemove(string, from, to) {
    if (to == 0)
        return string;

    var newString = string.substr(0, from) + string.substr(to);
    return newString;
}
function NUSAI_stringInsert(string, insertPos, text) {
    NUSAI_logInfo("NUSAI_stringInsert", string + "," + insertPos + "," + text);
    if (!string || string.lenght == 0)
        return text;

    var newString = string.substr(0, insertPos) + text + string.substr(insertPos);
    return newString;
}
function NUSAI_isChildAllowed(parentNode, childNode) {
    if (!NUSAI_isContainerElement(parentNode))
        return false;

    var parent = parentNode.nodeName.toLowerCase();
    var child = childNode.nodeName.toLowerCase();

    if (parent == "div")
        return true;

    if (parent == "p")
        return child != "p" && child != "div";

    if (child == "p")
        return false;

    return true;
}
function NUSAI_isContainerElement(node) {
    return node.nodeType != 3 && node.nodeName != "BR";
}
function NUSAI_hasParent(node, parentNodeName) {
    parentNodeName = parentNodeName.toLowerCase();
    while (node.parentNode) {
        node = node.parentNode;
        if (node.nodeName.toLowerCase() == parentNodeName)
            return true;
    }
    return false;
}
function NUSAI_getWrappedLineBreak() {
    var br = NUSAI_document.createElement("BR");
    var span = NUSAI_document.createElement("SPAN");
    span.appendChild(br);
    return span;
}
function NSUAI_getTrailingBreak(element) {
    if (!element.hasChildNodes())
        return null;

    var node = element;
    while (node.lastChild) {
        node = node.lastChild;
    }

    return node.nodeName == "BR" ? node : null;
}
function NUSAI_getNodesFromText(text) {
    var rcNodes = new Array();
    if (NUSAI_isMSExplorer)
        text = NUSAI_replaceAll(text, "\r", "");

    var nodes = text.split("\n");
    var lastParagraph = null;
    for (var i = 0; i < nodes.length; ++i) {
        var para = nodes[i].split("\f");
        for (var j = 0; j < para.length; ++j) {
            if (para[j].length > 0) {
                var textNode = NUSAI_document.createTextNode(para[j]);
                if (lastParagraph)
                    lastParagraph.appendChild(textNode);
                else
                    rcNodes.push(textNode);
            }
            if (j < para.length - 1) {
                var paraNode = NUSAI_document.createElement("P");
                rcNodes.push(paraNode);
                lastParagraph = paraNode;                
            }

        }
        if (i < nodes.length - 1) {
            var brNode = NUSAI_document.createElement("BR");
            if (lastParagraph)
                lastParagraph.appendChild(brNode);
            else
                rcNodes.push(brNode);
        }
    }

    return rcNodes;
}
function NUSAI_getNextNode(root, node) {
    if (!node)
        return null;

    if (node.childNodes && node.childNodes.length > 0)
        return node.childNodes[0];
    
    if (node == root)
        return null;

    if (node.nextSibling)
        return node.nextSibling;

    node = node.parentNode;
    if (node == root)
        return null;

    while (node && !node.nextSibling) {
        node = node.parentNode;
        if (node && node == root)
            return null;       
    }
    
    return node!=null ? node.nextSibling : null;
}
function NUSAI_insertNodeAfter(element, newNode, previousNode) {
    if (element == previousNode) {
        element.appendChild(newNode);
        return;
    }

    var parent;
    if (NUSAI_isContainerElement(previousNode) && !NUSAI_isContainerElement(newNode)) {
        parent = previousNode;
        previousNode = parent.lastChild;
    }
    else
        parent = previousNode.parentNode;

    while (!NUSAI_isChildAllowed(parent, newNode)) {
        var elementName = parent.nodeName;
        if (parent.lastChild == previousNode) {
            previousNode = parent;
            parent = parent.parentNode;
            continue;
        }
        var splitParent = NUSAI_document.createElement(elementName);
        if (previousNode) {
            var sibling = previousNode.nextSibling;
            while (sibling) {
                splitParent.appendChild(sibling);
                sibling = sibling.nextSibling;
            }
        }
        if (parent.nextSibling)
            parent.parentNode.insertBefore(splitParent, parent.nextSibling);
        else
            parent.parentNode.appendChild(splitParent);

        previousNode = parent;
        parent = parent.parentNode;               
    }

    if (previousNode && previousNode.nextSibling)
        parent.insertBefore(newNode, previousNode.nextSibling);    
    else 
        parent.appendChild(newNode);
}
function NUSAI_insertNodeBefore(element, node, newNode) {
    if (element == node || !node) {
        element.appendChild(newNode);
        return;
    }
    var parent = node.parentNode;
    while (!NUSAI_isChildAllowed(parent, newNode)) {
        var elementName = parent.nodeName;
        var splitParent = NUSAI_document.createElement(elementName);
        if (node) {
            splitParent.appendChild(node);
            var sibling = node.nextSibling;
            while (sibling) {
                splitParent.appendChild(sibling);
                sibling = sibling.nextSibling;
            }
        }

        if (parent.nextSibling)
            parent.parentNode.insertBefore(splitParent, parent.nextSibling);
        else
            parent.parentNode.appendChild(splitParent);

        parent = parent.parentNode;
        node = splitParent;
    }

    if (node)
        parent.insertBefore(newNode, node);
    else
        parent.appendChild(newNode);    
}
function NUSAI_splitTextNode(node, pos) {
    NUSAI_logTrace("NUSAI_splitTextNode", node.nodeName + "," +pos);
   
    var text = node.nodeValue;
    if (pos > text.length - 1)
        return node.nextSibling;

    var preText = text.substr(0, pos);
    var postText = text.substr(pos);
    var newTextNode = NUSAI_document.createTextNode(postText);
    node.nodeValue = preText;
    node.parentNode.insertBefore(newTextNode, node.nextSibling);
    return newTextNode;
}

function NUSAI_deleteNodes(element, fromPos, toPos) {
    NUSAI_logTrace("NUSAI_deleteNodes", fromPos+"-"+toPos);
    var startNodeRC = NUSAI_getNodeInfo(element,fromPos);
    var endNodeRC = NUSAI_getNodeInfo(element,toPos);
    var selStart = Number(fromPos) - startNodeRC.start;
    var selEnd = Number(toPos) - endNodeRC.start;
    NUSAI_logTrace("NUSAI_deleteNodes", "startNodeRC:" + startNodeRC.node.nodeName + "  - " + startNodeRC.node.nodeValue);
    NUSAI_logTrace("NUSAI_deleteNodes", "endNodeRC:" + endNodeRC.node.nodeName + "  - " + endNodeRC.node.nodeValue);
    NUSAI_logTrace("NUSAI_deleteNodes", "selection:" + selStart + ":" + selEnd);

    if (startNodeRC.node == endNodeRC.node) {
        startNodeRC.node.nodeValue = NUSAI_stringRemove(startNodeRC.node.nodeValue, selStart, selEnd);
        return;
    }

    startNodeRC.node.nodeValue = startNodeRC.node.nodeValue.substr(0, selStart);
    var trashBin = new Array();
    var node = NUSAI_getNextNode(element, startNodeRC.node);
    while (node) {
        var parent = node.parentNode;
        NUSAI_logTrace("NUSAI_deleteNodes", "node:" + node.nodeName + " - " + node.nodeValue);
        if (node == endNodeRC.node) {
            endNodeRC.node.nodeValue = endNodeRC.node.nodeValue.substr(selEnd);
            break;
        }

        trashBin.push(node);

        node = NUSAI_getNextNode(element, node);
    }
    for (var i=0; i<trashBin.length;++i) {
        if (!trashBin[i].hasChildNodes())
            trashBin[i].parentNode.removeChild(trashBin[i]);        
    }
}

// if stopNode!=null, position is ignored and nodeInfo before stopNode is returned
function NUSAI_getNodeInfoInternal(element, currentNode, rc, position, stopNode) {
    if (!currentNode || rc.nodeFound)
        return rc;

    var startPos = rc.end;
    var endPos = rc.end;
    var nodeText = "";

    if (currentNode == stopNode) {
        rc.nodeFound = true;
        return rc;
    }
    if (!stopNode && position < startPos) {
        rc.nodeFound = true;
        return rc;
    }

    if (currentNode.nodeValue) {
        nodeText = currentNode.nodeValue;
        endPos = startPos + nodeText.length;
    }
    else {
        if (!stopNode && position == startPos && currentNode.nodeName == "BR") {
            rc.nodeFound = true;
            return rc;
        }
        var lineBreaks = NUSAI_nodeNeedsLineBreak(currentNode);
        if (lineBreaks > 0 && rc.textBefore.length > 0) {
            var addLineBreaks = false;
            if (NUSAI_isMSExplorer)
                addLineBreaks = !NUSAI_textEndsWithContainerBreak(rc.textBefore);
            else 
                addLineBreaks = !NUSAI_textEndsWithLineBreak(rc.textBefore) && !NUSAI_textEndsWithContainerBreak(rc.textBefore);

            if (addLineBreaks) {
                rc.end += lineBreaks;
                rc.textBefore += NUSAI_getContainerBreakText();
                startPos = endPos = rc.end;                                
            }
        }
        else if (NUSAI_getLineBreaksForNode(element, currentNode)) {
            endPos += NUSAI_getLineBreaksForNode(element, currentNode);
            nodeText += NUSAI_getLineBreakText();
        }
    }

    if (!stopNode && position >= startPos && position <= endPos) {
        rc.node = currentNode;
        rc.start = startPos;
        rc.end = endPos;
        if (currentNode.nodeType == 3) {
            rc.nodeFound = true;
            return rc;
        }
    }

    rc.end = endPos;
    rc.textBefore += nodeText;
    if (currentNode.childNodes && currentNode.childNodes.length > 0) {
        var tmpRc = rc;
        for (var j = 0; j < currentNode.childNodes.length; ++j) {
            tmpRc = NUSAI_getNodeInfoInternal(element, currentNode.childNodes[j], tmpRc, position, stopNode);
            if (tmpRc.nodeFound) {
                return rc;
            }
            if (!stopNode && tmpRc.node && tmpRc.node.nodeType == 3 && position >= tmpRc.start && position <= tmpRc.end) 
                return tmpRc;
            
            rc = tmpRc;
        }
    }

       
    if (NUSAI_nodeNeedsLineBreak(currentNode) && !(NUSAI_textEndsWithLineBreak(rc.textBefore) || NUSAI_textEndsWithContainerBreak(rc.textBefore))) {
        rc.end += NUSAI_getLineBreakText().length;
        rc.textBefore += NUSAI_getContainerBreakText();
    }

    return rc;
}

function NUSAI_getNodeInfo(element, position, node) {
    var rc = new NUSAI_nodeInfoEx();
    rc.start = 0;
    rc.end = 0;
    rc.node = null;
    rc.textBefore = "";
    rc.nodeFound = false;
    rc = NUSAI_getNodeInfoInternal(element, element, rc, position, node);
    rc.textBefore = NUSAI_replaceAll(rc.textBefore, NUSAI_getContainerBreakText(), NUSAI_getParagraphText());
    return rc;
}

function NUSAI_getContentEditableText(root, node, text) {
    if (!node) {
        NUSAI_logTrace("NUSAI_getContentEditableText", "node is null");
        return text;
    }

    if (node.nodeType == 3 && node.nodeValue.length > 0) {
        text += node.nodeValue;
    }
    else if (NUSAI_getLineBreaksForNode(root, node)) {
        text += NUSAI_getLineBreakText();
    }

    if (node.childNodes && node.childNodes.length > 0) {
        for (var j = 0; j < node.childNodes.length; ++j) {
            text = NUSAI_getContentEditableText(root, node.childNodes[j], text);
        }
    }

    if (root != node) {
        var nextNode = node.nextSibling;
        if (nextNode) {
            var isNextNodeEmptyText = nextNode.nodeType == 3 && (!nextNode.nodeValue || nextNode.nodeValue == "");
            var isNodeEmptyText = node.nodeType == 3 && (!node.nodeValue || node.nodeValue == "");
            if (NUSAI_nodeNeedsLineBreak(node)) {
                if (!isNextNodeEmptyText)
                    text += NUSAI_getLineBreakText();
            }
            else if (!NUSAI_getLineBreaksForNode(root, node) && !isNodeEmptyText) {
                if (NUSAI_nodeNeedsLineBreak(nextNode))
                    text += NUSAI_getLineBreakText();
            }
        }
    }
    return text;
}/// text.js //////////////////////////////////////
//////////////////////////////////////////////////
var NUSAI_selectionInfo = function () {
    this.start;
    this.length;
}
function NUSAI_getLineBreakText() {
    if (NUSAI_isMSExplorer)
        return NUSAI_getLineBreakTextIE();
    return NUSAI_getLineBreakTextMozilla();
}
function NUSAI_getContainerBreakText() {
    if (NUSAI_isMSExplorer)
        return NUSAI_getContainerBreakTextIE();
    return NUSAI_getContainerBreakTextMozilla();
}
function NUSAI_getParagraphText() {
    if (NUSAI_isMSExplorer)
        return NUSAI_getParagraphTextIE();
    return NUSAI_getParagraphTextMozilla();
}
function NUSAI_getLineBreaksForNode(element, node) {
    if (NUSAI_isMSExplorer)
        return NUSAI_getLineBreaksForNodeIE(node);
    return NUSAI_getLineBreaksForNodeMozilla(element, node);
}
function NUSAI_nodeNeedsLineBreak(node) {
    if (NUSAI_isMSExplorer)
        return NUSAI_nodeNeedsLineBreakIE(node);
    return NUSAI_nodeNeedsLineBreakMozilla(node);
}
function NUSAI_getText(element) {
    if (element == null)
        return "";
    
    if (NUSAI_isMSExplorer)
        return NUSAI_getTextIE(element);

    return NUSAI_getTextMozilla(element);
}
function NUSAI_getSelectionText(element) {
    if (element == null)
        return "";

    if (NUSAI_lastFocusedElement != element)
        return "";

    if (NUSAI_isMSExplorer)
        return NUSAI_getSelectionTextIE(element);
    
    return NUSAI_getSelectionTextMozilla(element);
}
function NUSAI_replaceText(element, text, fromPos, toPos ) {
    if (element == null)
        return;

    if (NUSAI_isMSExplorer)
        return NUSAI_replaceTextIE(element, text, fromPos, toPos);

    return NUSAI_replaceTextMozilla(element, text, fromPos, toPos);
}
function NUSAI_getSelection(element) {
    var info = new NUSAI_selectionInfo();
    info.start = 0;
    info.length = 0;

    if (element == null)
        return info;
    
    var elementText = NUSAI_getText(element);
    if (!elementText || elementText.length == 0) {
        NUSAI_logTrace("NUSAI_getSelection()", "element empty");
        return info;
    }

    if (NUSAI_lastFocusedElement != element) {
        NUSAI_logTrace("NUSAI_getSelection()", "element not focussed");
        info.start = elementText.length;
        return info;
    }

    if (NUSAI_isMSExplorer)
        return NUSAI_getSelectionIE(element);

    return NUSAI_getSelectionMozilla(element);
}

function NUSAI_setSelection(element, start, length) {   
    if (element == null)
        return;

    element.focus();

    NUSAI_logTrace("NUSAI_setSelection()", element.id + "/" + start + "/" + length);

    var text = NUSAI_getText(element);
    if (start > text.length) {
        start = text.length;
        NUSAI_logInfo("NUSAI_setSelection()", "moving cursor to end");
    }

    if (NUSAI_isMSExplorer) {
        NUSAI_lastFocusedElement = element;
        NUSAI_setSelectionIE(element, start, length);
    }
    else
        NUSAI_setSelectionMozilla(element, start, length);

}
/// report.js //////////////////////////////////////
////////////////////////////////////////////////////

var NUSAI_ChangeRegion = function () {
    this.Start;
    this.lengthBefore;
    this.lengthAfter;
}
function NUSAI_computeChanges(oldText, newText) {
    var start = 0;
    if (!oldText)
        oldText = "";
	if (!newText)
        newText = "";

	while (start < oldText.length && start < newText.length && oldText[start] == newText[start])
        ++start;

    //
    // Compute length of change region and length of new text
    //
    var lengthBefore;
    var lengthAfter;

    // oldText is a prefix of newText
    // (= strings are identical or characters were added to the end of oldText)
    if (start == oldText.length) {
        
        // Strings are identical - no changes found
        if (oldText.length == newText.length)
            return null;
        
        lengthBefore = 0;
        lengthAfter = newText.length - start;
    }
    // newText is a prefix of oldText
    // (= characters were deleted from the end of oldText)
    else if (start == newText.length) {
        lengthBefore = oldText.length - start;
        lengthAfter = 0;
    }
    // At least one character (at position Start) was changed
    else {
        // Search for first changed character starting at the end of the strings
        // and going backwards towards "Start"
        var i = oldText.length - 1;
        var j = newText.length - 1;
        while ( start <= i && start <= j && oldText[i] == newText[j] ) {
            --i; --j;
        }
        lengthBefore = i + 1 - start;
        lengthAfter = j + 1 - start;
    }

    //
    // Return found change region
    //
    var changeRegion = new NUSAI_ChangeRegion();
    changeRegion.start = start;
    changeRegion.lengthBefore = lengthBefore;
    changeRegion.lengthAfter = lengthAfter;
    return changeRegion;
}


function NUSAI_addClass(element, className) {
    if (!NUSAI_hasClass(element, className)) {
        if (element.className) {
            element.className += " " + className;
        } else {
            element.className = className;
        }
    }
}

function NUSAI_removeClass(element, className) {
    if (!NUSAI_hasClass(element, className))
        return;

    var regexp = new RegExp("(^|\\s)" + className + "(\\s|$)");   
    element.className = element.className.replace(regexp, "$2");
}

function NUSAI_hasClass(element, className) {
    if (!element || !element.className)
        return false;

    var regexp = new RegExp("(^|\\s)" + className + "(\\s|$)");
    return regexp.test(element.className);
}

function NUSAI_onFocusableClicked(e) {
    var target = NUSAI_getEventTarget(e);
    var sfId = target.attributes[NUSAI_Id].value;
    if (!sfId || sfId.length==0) {
        NUSAI_logWarning("NUSAI_onFocusableClicked", target + ": no sfId");
        return;
    }
    var id = NUSAI_ids["_" + sfId];
    if (!id)
        id = NUSAI_ids[sfId];
    
    NUSAI_selectNode(id);
}
function NUSAI_getscrollPosY() {
    var scrollYPos = 0;
    if (window.pageYOffset)
        scrollYPos = window.pageYOffset;
    else if (NUSAI_document.documentElement.scrollTop)
        scrollYPos = NUSAI_document.documentElement.scrollTop;

    else
        scrollYPos = NUSAI_document.body.scrollTop;

    return scrollYPos;
}
function NUSAI_getscrollPosX() {
    var scrollXPos = 0;
    if (window.pageXOffset)
        scrollXPos = window.pageXOffset;
    else if (NUSAI_document.documentElement.scrollLeft)
        scrollXPos = NUSAI_document.documentElement.scrollLeft;

    else
        scrollXPos = NUSAI_document.body.scrollLeft;

    return scrollXPos;
}
function NUSAI_ensureVisible(topElement, bottomElement) {
    NUSAI_logTrace('NUSAI_ensureVisible', (topElement ? topElement.id : "null") + "," + (bottomElement ? bottomElement.id :  "null"));
    var selectedPosX = 0;
    var selectedPosY = 0;
    var temp = topElement;
    while (temp != null) {
        NUSAI_logTrace('NUSAI_ensureVisible', "getting offset parent:" + temp.id ? temp.id : temp.tagName);
        selectedPosX += temp.offsetLeft;
        selectedPosY += temp.offsetTop;
        temp = temp.offsetParent;
    }

    NUSAI_logTrace('NUSAI_ensureVisible', "selected topElement pos: " + selectedPosX + "/" + selectedPosY);
    var scrollPosY = NUSAI_getscrollPosY();
    var scrollPosX = NUSAI_getscrollPosX();
    var windowHeight = window.innerHeight;
    NUSAI_logTrace('NUSAI_ensureVisible', "scroll pos: " + scrollPosX + "/" + scrollPosY);
 
    if (selectedPosY < scrollPosY) {// element above window
        window.scrollTo(scrollPosX, selectedPosY - 20);
        NUSAI_logTrace('NUSAI_ensureVisible', "element above window"); 
        return;
    }
    if (bottomElement) {
        selectedPosX = 0;
        selectedPosY = 0;
        tmp = bottomElement;
        while (tmp != null) {
            selectedPosX += tmp.offsetLeft;
            selectedPosY += tmp.offsetTop;
            tmp = tmp.offsetParent;
            NUSAI_logTrace('NUSAI_ensureVisible', "offsetParent=" + tmp); 
        }
    }
    else
        bottomElement = topElement;

    NUSAI_logTrace('NUSAI_ensureVisible', "selected bottomElement pos: " + selectedPosX + "/" + selectedPosY);

    var windowHeight;
    if (window.innerHeight) 
        windowHeight = window.innerHeight;
    else
        windowHeight = document.body.clientHeight;

    windowHeight -= 15;
    NUSAI_logTrace('NUSAI_ensureVisible', "windowHeight-15=" + windowHeight);
    var maxBottom = scrollPosY + windowHeight;
    NUSAI_logTrace('NUSAI_ensureVisible', "bottomElement.offsetHeight=" + bottomElement.offsetHeight); 
    selectedPosY += bottomElement.offsetHeight;    
    if (selectedPosY > maxBottom) {
        var pos = selectedPosY - windowHeight;
        window.scrollTo(scrollPosX, pos);
        NUSAI_logTrace('NUSAI_ensureVisible', "element below window"); 
    }
}

function NUSAI_onElementFocus(e) {
    var target = NUSAI_getEventTarget(e);

    NUSAI_lastFocusedElement = target;

    var sfId = target.attributes[NUSAI_Id];

    if (!sfId || sfId.value.length == 0 || NUSAI_lastDanubeFocusId != sfId.value) {
        NUSAI_storeCurrentRequestId();
    }

    if (!sfId || sfId.value.length == 0) {
        if (NUSAI_mode == NUSAI_mode_navigation)
            NUSAI_StartNavigation();
        NUSAI_logInfo("NUSAI_onElementFocus",target + ": no sfId -> ignored");
        return;
    }

    var isText = target.type == "textarea" || target.type == "text" || NUSAI_isContentEditable(target);

    if (NUSAI_mode == NUSAI_mode_navigation) {
        if (isText)
            NUSAI_StartDictation();
        else
            NUSAI_StartNavigation();
    }

    NUSAI_selectNode(target.attributes['id'].value);
}
function NUSAI_setActivePath(address) {

    NUSAI_logInfo('NUSAI_setActivePath', address);

    var element;
    var id = NUSAI_ids[address];

    while (id && (element = NUSAI_document.getElementById(id)) != null) {
        NUSAI_addClass(element, NUSAI_classActive);
        address = address.substr(0, id.length - 1);
        id = NUSAI_ids[address];
    }
}

function NUSAI_updateText(requestId, element, newText, changeRegion, selectionStartBefore, selectionLengthBefore, selectionStartAfter, selectionLengthAfter) {
    try {
        NUSAI_logEnter("NUSAI_updateText", element.id + ",before:" + selectionStartBefore + "/" + selectionLengthBefore + ",after:" + selectionStartAfter + "/" + selectionLengthAfter);
        var newSelectionStart = -1;
        var newSelectionLength = -1;
        var insertionLocationChanged = false;
        var focussed = NUSAI_lastFocusedElement == element;
        var selectionInfo = NUSAI_getSelection(element);
        var selectionStart = selectionInfo.start;
        var selectionLength = selectionInfo.length;
        var insertPosition = selectionStartBefore;       

        try {
            if (NUSAI_utteranceHistory && NUSAI_utteranceHistory.length > 0) {
                for (var i = 0; i < NUSAI_utteranceHistory.length; ++i) {
                    if (NUSAI_utteranceHistory[i].requestId != requestId)
                        continue;

                    var info = NUSAI_utteranceHistory[i];
                    var noChange = selectionStart == info.selectionStart && selectionLength == info.selectionLength;
                    // check if SM adjusted the selection; if the user did not change it, take the SM values
                    if (noChange && selectionStartBefore != info.selectionStart || selectionLengthBefore != info.selectionLength) {
                        selectionStart = selectionStartBefore;
                        selectionLength = selectionLengthBefore;
                        NUSAI_logTrace("NUSAI_updateText", "adjusting selection to SM values");
                    }
                }
            }
            else
                NUSAI_logWarning("NUSAI_updateText", "No utteranceHistory for " + requestId);
        }
        catch (error) {
            NUSAI_logError("NUSAI_updateText", "UtteranceHistory: " + error);
        }


        NUSAI_logInfo("NUSAI_updateText", "current selection:" + selectionStart + "/" + selectionLength);

        // Text up to insertion position is unchanged: Apply text event
        // (Either no changes or changes occurred to the right of the region modified by the text event.)
        if (changeRegion == null ||
                (selectionStartBefore < changeRegion.start &&
                selectionStartBefore + selectionLengthBefore < changeRegion.start)) {
            newSelectionStart = selectionStartAfter;
            newSelectionLength = selectionLengthAfter;
            NUSAI_replaceText(element, newText, selectionStartBefore, selectionStartBefore + selectionLengthBefore);
        }
        // Editing modified characters strictly to the left of the dictation location:
        // Shift insertion location
        else if (changeRegion.start + changeRegion.lengthBefore < selectionStartBefore) {
            var shiftFromEditing = changeRegion.lengthAfter - changeRegion.lengthBefore;

            NUSAI_logInfo("NUSAI_updateText", "Characters shifted due to editing: " + shiftFromEditing);

            newSelectionStart = selectionStartAfter + shiftFromEditing;
            newSelectionLength = selectionLengthAfter;

            insertPosition = selectionStartBefore + shiftFromEditing;
            NUSAI_replaceText(element, newText, insertPosition, insertPosition + selectionLengthBefore);
        }
        // Editing modified insertion position: insert at current selection, but do not move selection
        else {
            NUSAI_logInfo("NUSAI_updateText", "Editing [" + changeRegion.start + ", " + (changeRegion.start + changeRegion.lengthBefore) + "] changed insertion position [" + selectionStartBefore + ", " + (selectionStartBefore + selectionLengthBefore) + "]");

            var selInfo = NUSAI_getSelection(element);
            newSelectionStart = selInfo.start;
            newSelectionLength = selInfo.length;
            insertPosition = newSelectionStart + newSelectionLength;
            NUSAI_replaceText(element, newText, insertPosition, insertPosition);
            insertionLocationChanged = true;
        }
        // If insertion location was changed:
        // * Do not move the selection location
        // * Send a server update before next utterance
        //   @TODO: Inform server about this in a clean manner? (for improved adaptation?)
        if (insertionLocationChanged) {
            changeRegion.start = -1;
        }

        if (focussed) { // restore cursor to previous position
            if (selectionLengthBefore == 0) { // no overwriting - simply restore
                if (selectionStart >= insertPosition)
                    selectionStart += newText.length;
            } // exact overwriting - move to end of result
            else if (selectionStartBefore == selectionStart && selectionLengthBefore == selectionLength) {
                selectionStart = insertPosition + newText.length;
                selectionLength = 0;
            }
            else { // text part is overwritten
                if (selectionStart <= insertPosition) { // selection starts to the left - keep startPos
                    NUSAI_logInfo("NUSAI_updateText", "selection starts to the left - keep startPos");
                    if ((selectionStart + selectionLength) > insertPosition) // selection ends at insertPos
                        selectionLength = insertPosition - selectionStart;
                }
                else if (selectionStart <= (insertPosition + selectionLengthBefore)) { // selection starts inside
                    var tmp = insertPosition + newText.length;    // move startPos to end of result
                    if (selectionLength > 0) {
                        var endPosition = selectionStart + selectionLength;
                        var endPositionBefore = selectionStartBefore + selectionLengthBefore;
                        if (endPosition == endPositionBefore)
                            selectionLength = selectionLengthAfter;
                        else
                            selectionLength = (endPosition > endPositionBefore) ? endPosition - endPositionBefore : 0;
                    }
                    selectionStart = tmp;
                }

                else {// selection starts to the right
                    NUSAI_logInfo("NUSAI_updateText", "selection starts to the right");
                    selectionStart += newText.length; // move start, keep length
                }

            }

            NUSAI_logInfo("NUSAI_updateText", "restore cursor:" + selectionStart + "/" + selectionLength);
            NUSAI_setSelection(element, selectionStart, selectionLength);
        }
        else
            NUSAI_logInfo("NUSAI_updateText", "element not focussed");

    }
    finally {
        NUSAI_logExit("NUSAI_updateText");
    }
}
function NUSAI_replaceTextInternal(requestId, address, value, textBefore, selectionStartBefore, selectionLengthBefore, selectionStartAfter, selectionLengthAfter) {
    try {
        NUSAI_logEnter('NUSAI_replaceTextInternal', requestId + ', ' + address);

        var element = NUSAI_document.getElementById(address);
        if (element == null)
            return;

        var sfId = element.attributes[NUSAI_Id].value;

        var scrollTop = element.scrollTop;
        var scrollLeft = element.scrollLeft;
        var scrollHeight = element.scrollHeight;
        var scrollWidth = element.scrollWidth;

        NUSAI_logTrace("NUSAI_replaceTextInternal", "top:" + scrollTop + " height:" + scrollHeight);

        var changeRegion = NUSAI_computeChanges(textBefore, NUSAI_getText(element));
        NUSAI_updateText(requestId, element, value, changeRegion, selectionStartBefore, selectionLengthBefore, selectionStartAfter, selectionLengthAfter);

        var elementText = NUSAI_getText(element);

        NUSAI_logTrace("NUSAI_replaceTextInternal", "top:" + element.scrollTop + " height:" + element.scrollHeight);

        var info = NUSAI_getSpeechFieldInfo(sfId);
        info.selectionStart = selectionStartAfter;
        info.selectionLength = selectionLengthAfter;
        info.text = value;

        var selEndPos = info.selectionStart + info.selectionLength;
        if (selEndPos == 0) {
            NUSAI_logTrace("NUSAI_replaceTextInternal", "scroll to start:");
            element.scrollTop = 0;
            element.scrollLeft = 0;
        }
        else if (selEndPos == elementText.length) {
            NUSAI_logTrace("NUSAI_replaceTextInternal", "scroll to end:" + selEndPos);
            element.scrollTop = element.scrollHeight;
            // we don't know about element.scrollLeft
        }
        else {
            var newScrollTop = scrollTop + element.scrollHeight - scrollHeight;
            NUSAI_logTrace("NUSAI_replaceTextInternal", "scroll to:" + newScrollTop);
            element.scrollTop = newScrollTop;
            element.scrollLeft = scrollLeft + element.scrollWidth - scrollWidth;
        }
    }
    finally {
        NUSAI_logExit('NUSAI_replaceTextInternal');
    }
}
function NUSAI_markNode(elementId) {
    if (NUSAI_lastFocusedSpeechElementId != null) {
        var lastElement = NUSAI_document.getElementById(NUSAI_lastFocusedSpeechElementId);
        NUSAI_removeClass(lastElement, NUSA_focusedElement);
    }
    var element = NUSAI_document.getElementById(elementId);
    if (NUSAI_isRecording)
        NUSAI_addClass(element, NUSA_focusedElement);
}

function NUSAI_selectNode(elementId) {
    NUSAI_logInfo("NUSAI_selectNode", elementId);

    if (NUSAI_lastFocusedSpeechElementId == elementId)
        return;

    if (NUSAI_isRecording)
        NUSAI_ForceFlush();

    var element = NUSAI_document.getElementById(elementId);

    NUSAI_markNode(elementId);

    if (NUSAI_bubbleContainer)
        NUSAI_addLogoToElement(element, false);

    if (NUSAI_lastFocusedSpeechElementId) {
        var lastFocusedSpeechElement = NUSAI_document.getElementById(NUSAI_lastFocusedSpeechElementId);
        NUSAI_addLogoToElement(lastFocusedSpeechElement, NUSAI_hasClass(lastFocusedSpeechElement, NUSAI_classResultPending));
    }

    NUSAI_lastFocusedSpeechElementId = elementId;    

    NUSAI_speechElementFocussed();

    NUSAI_ensureVisible(element, NUSAI_bubbleContainer);

    NUSAI_logInfo("NUSAI_selectNode", "type:" + element.type);

    if (element.type != "text" && element.type != "textarea" && !NUSAI_isContentEditable(element)) {
        element.focus();
        return;
    }

    if (NUSAI_lastFocusedElement != element) {
        element.focus();
        // when calling focus(), cursor is always at the beginning
        // not the same as using TAB
        NUSAI_setSelection(element, NUSAI_getText(element).length, 0);
    }
}
