// based on monier/alt-main.js
// Oct 11, 2012. Revised to use jQuery for Ajax and DOM
// Removed 'queryInputChar' function (formerly used as keydown handler)
// Removed standard_input
// Oct 15, 2012.  Modified to work with transcoderjs.
// May 15, 2023.  keyboard_parms:  escape deprecated. Use encodeURI.
jQuery(document).ready(function(){ 
 theTranscoderField = new TranscoderField('key1',keydown_return);
 win_ls=null;
 VKI.transcoderInit();
 jQuery('#disp').html("");
});
VKI.transcoderInit = function() {
 VKI.transcoderField = theTranscoderField; // add new attribute to VKI.
 jQuery('#key1').attr('class','keyboardInput'); // needs to precede VKI.load!
 VKI.load();
 transcoderChange(); // install the in/out preferences initialized by VKI.load
 jQuery('#disp').html("");
 //jQuery('#key1').keydown(keyboard_HandleChar);
 jQuery('#preferenceBtn').click(preferenceBtnFcn);
};

function preferenceBtnFcn(event) {
    showPreferences(); // from keyboard.js
}
function transcoderChange() {
 // Get inval/outval parms from VKI.state via the cookies
 // Note that 'inputType' should be 'phonetic' for the logic to work.
 // At the moment (Oct 15, 2012) the 
 // Note of Oct 16, 2012.  This function is called by
 // (a) the 'ready' function
 // (b) the 'okBtn' function in preferences.htm
  var inputType = readCookie("inputType"); 
  var phoneticInput = readCookie("phoneticInput"); 
  var viewAs = readCookie("viewAs");
 /*
  var inputType = VKI.state.inputType;
  var phoneticInput = VKI.state.phoneticInput;
  var viewAs = VKI.state.viewAs;
 */
  var inval = phoneticInput;
  if (inval == 'it') {inval = 'itrans';}
  var outval = viewAs;
  if ((inputType == 'phonetic') && (outval == 'phonetic')) {
      outval = inval;
  }
  //console.log('transcoderChange: ',inputType,viewAs,inval,outval);
  theTranscoderField.transCoderChange(inval,outval);
}

var requests = new Array();
var requestsActive = new Array();

for (var i=0;i<2;i++) {
try {
  requests[i] = new XMLHttpRequest();
} catch (trymicrosoft) {
  try {
    requests[i] = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (othermicrosoft) {
    try {
      requests[i] = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (failed) {
      requests[i] = null;
    }
  }
}

    if (requests[i] == null){
  alert("Error creating request object!");
    }
 requestsActive[i]=false;
}
//alert("have set requests");
var getlistFlag = false;
function keydown_return() {
 getWord_keyboard(false,false); 
}
 function unused_keyboard_HandleChar(event) {
 // Handler for keydown event
 // presumably, if RETURN key not pressed, next statement passes along
 // the keystroke to some other handler (e.g. that of VKI_devanalysis).
 
 if (event.keyCode != 13) return;
 getWord_keyboard(false,false); 
 // not sure why this stuff here
 if (event.stopPropagation) 
  event.stopPropagation();
 else event.cancelBubble = true;
 if (event.preventDefault) event.preventDefault();
 else event.returnValue = false;
 }

function getWordAlt_keyboard(keyserver) {
 // might be a problem if view differs from server/display
//    document.getElementById("key1").value = keyserver; //chg1
    getWord_keyboard("NO",keyserver);  //chg1
}
function getWordlist_keyboard() {
    var url =   keyboard_parms(false,true);
//    alert("getWordlist_keyboard: url="+url);
    getWordlist_main(url);
}
function getWordlistUp_keyboard(keyserver) {
    var url =    keyboard_parms(keyserver,true) + 
              "&direction=UP";
    getWordlist_main(url);
}
function getWordlistDown_keyboard(keyserver) {
    var url =  keyboard_parms(keyserver,true) + 
              "&direction=DOWN";
    getWordlist_main(url);
}
function getWord_keyboard(listFlag,keyserver) {
    var url =  keyboard_parms(keyserver,false); //chg1
    //console.log('getWord_keyboard: url=',url);
    getWord_main(url);
    if(listFlag == "NO") {
    getlistFlag = false;
    }else {
     getlistFlag = true;
     getWordlist_keyboard();
    }
}
function getWord_main(url) {
    try {
  requests[0].open("GET", url, true);
    requests[0].onreadystatechange = updateDisp;
    requests[0].send(null);
    requestsActive[0]=true;
    jQuery('#disp').html("");
    } catch (failed){
	alert("getWord_main error");
    }
}
function getWordlist_main(url) {
  requests[1].open("GET", url, true);
    requests[1].onreadystatechange = updateDisplist;
    requests[1].send(null);
    requestsActive[1]=true;
    document.getElementById("displist").innerHTML = '';
//    '<p>working...</p>' ;
}
function keyboard_parms(keyserver,listurlFlag) {  
    var word,inputType,unicodeInput,phoneticInput,viewAs,serverOptions,accent;
    if (keyserver) {
     // 'keyserver' is a word passed as a parameter when the user
     //  clicks on a 'list' word.  In this case the 'viewAs' parameter
     //  has the value of the 'serverOptions' parameter
     word = keyserver;
     inputType = readCookie("inputType");
     unicodeInput = readCookie("unicodeInput");
     phoneticInput = readCookie("phoneticInput");
     viewAs = readCookie("viewAs");
     serverOptions = readCookie("serverOptions");
     //accent = readCookie("accent");
     viewAs = serverOptions;
    }else {
     word = document.getElementById("key1").value;
     inputType = readCookie("inputType");
     unicodeInput = readCookie("unicodeInput");
     phoneticInput = readCookie("phoneticInput");
     viewAs = readCookie("viewAs");
     serverOptions = readCookie("serverOptions");
     //accent = readCookie("accent");
     // serverOptions = viewAs;  // Nov. 22, 2010
    }
    accent = "no";
    if (document.getElementById("accent")) {
      accent = document.getElementById("accent").value;
    }
    var url;
    if (listurlFlag) {
     var listOptions = readCookie("listOptions");
     listOptions = 'hierarchical'; // always
     if (listOptions == 'hierarchical') { // display list of nearby headwords
  	url = "listhier.php";
     }/*else {
	url = "monierlist.php"; // alphabetical
     }*/
    }else { // display of main entry for $key
	url = "disphier.php";

    }
   var ans = 
   url + 
   "?key=" +  word + 
   "&keyboard=" + "yes" +
   "&inputType=" + inputType +
   "&unicodeInput=" + unicodeInput +
   "&phoneticInput=" + phoneticInput +
   "&serverOptions=" + serverOptions +
   "&accent=" +  accent +
   "&viewAs=" +  viewAs;
    let ans1 = encodeURI(ans);
    //console.log('webtc1/main.js',url);
    return ans1;
}

function updateDisp() {
  if (requests[0].readyState == 4) {
   requestsActive[0]=false;
   if (requests[0].status == 200) {
       
    var response = requests[0].responseText;
    var ansEl = document.getElementById("disp");
//    alert('data ready for display...' + response);
    ansEl.innerHTML = response;
    // Dec 6, 2013
    var filter = readCookie("serverOptions");
      // kick off the next ajax request
 //debug      if(getlistFlag){getWordlist_keyboard();}    
//    return;
   }else if (requests[0].status == 0) { // needed for Firefox Nov 24, 2013
  } else {
    alert("Error! Request status is " + requests[0].status);
  }
 }
}
function updateDisplist() {
  if (requests[1].readyState == 4) {
   requestsActive[1]=false;
   if (requests[1].status == 200) {
    var response = requests[1].responseText;
    var ansEl = document.getElementById("displist");
//    alert('data ready for display...' + response);
    ansEl.innerHTML = response;
//    return;
   }else if (requests[0].status == 0) { // needed for Firefox Nov 24, 2013
  } else {
    alert("Error! Request status is " + requests[1].status);
  }
 }
}
function winls(url,anchor) {
// Called by a link made by basicdisplay.php
 var url1 = '../mwauth/'+url+'#'+anchor;
 win_ls = window.open(url1,
    "winls", "width=520,height=210,scrollbars=yes");
 win_ls.focus();
}
/* 06-19-2024 
 functions listhier_lnum_output and listhier_lnum_output1 are modeled after
 php functions getParameters_keyboard() and getParameters_keyboard_helper
 in listparm.php.
 This allows us to get the 'output' parm required by listhier.php.
*/
function listhier_lnum_output1(serverOptions,phoneticInput) {
 if (serverOptions == 'deva') {return serverOptions;}
 if (serverOptions == 'roman') {return serverOptions;}
 if (serverOptions == 'phonetic') {
  if (phoneticInput == 'slp1') {return phoneticInput;}
  if (phoneticInput == 'hk') {return phoneticInput;}
  //if (phoneticInput == 'it') {return phoneticInput;}
  if (phoneticInput == 'it') {return 'itrans';}
  if (phoneticInput == 'wx') {return phoneticInput;}
 }
 // default: 
 return "slp1";
}
function listhier_lnum_output() {
    // Use keyboard.js readCookie
    let phoneticInput = readCookie('phoneticInput');
    //console.log('phoneticInput=',phoneticInput);
    let serverOptions = readCookie('serverOptions');
    //console.log('serverOptions=',serverOptions);
    let output = readCookie('output');
    let output1 = listhier_lnum_output1(serverOptions,phoneticInput);
    //console.log(' output1 = ',output1);
    return output1;
}
function listhier_lnum(lnum,link) {
    //console.log('webtc1/main.js. listhier_lnum. lnum=',lnum);
    var word,input,output,accent,dict;
     //input = readCookie("input");  
     // By listhier logic, this use of input is always slp1
     input = 'slp1';
     //output = readCookie("output");
    output = listhier_lnum_output();
     accent = readCookie("accent");
     dict = readCookie("dict");
    var urlbase= "listhier.php";
    
   var accent = readCookie("accent");
   var url = 
   urlbase + 
   "?lnum=" +escape(lnum)+ 
   "&filterin=" + escape(input) +
   "&filter=" + escape(output) +
   "&accent=" + escape(accent) +
   "&dict=" + escape(dict);
    //var $this =$(this);  // the link in 'disp' that was clicked
    //console.log('listhier_lnum: url=\n',url);
    getWordlist_link(url,link);
}
function getWordlist_link(url,$link) {
    //console.log('webtc1/main.js. url=',url);
    //console.log(' ... $link=',$link);
    jQuery.ajax({
	url:url,
	type:"GET",
        success: function(data,textStatus,jqXHR) {
            jQuery("#displist").html(data);

            
            adjust_main_links($link); // 
	},
	error:function(jqXHR, textStatus, errorThrown) {
	    alert("Error: " + textStatus);
	}
    });
}
function adjust_main_links($link) {
    jQuery('.listlink').removeClass('listlinkCurrent');
    jQuery($link).addClass('listlinkCurrent');
}
/*
Warning: Undefined array key "keyboard" in C:\xampp\htdocs\cologne\mw\web\webtc1\listparm.php on line 43
*/
