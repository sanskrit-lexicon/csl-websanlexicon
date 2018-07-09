// web/webtc2/main.js
// Mar 29, 2014.  Changed escape to encodeURIComponent, and

function getWord() {
  lastLnum=0;
  jQuery("#nextbtn").hide();
  outopt = document.getElementById("outopt").value;
  getNext();
}
function getNext() {
  var word = "";
  if (document.getElementById("word").value) {
    word = document.getElementById("word").value;
  }
  var sword = "";
  if (document.getElementById("sword").value) {
    sword = document.getElementById("sword").value;
  }
  var regexp = document.getElementById("regexp").value;
  if ((word.length < 1) && (sword.length < 1)) {
   alert('Please specify a Sanskrit or an English word.');
   return;
  }
  if ((0<word.length) && (0<sword.length)) {
   alert('Please specify a Sanskrit or an English word, not both.');
   return;
  }
  
  var filter = document.getElementById("filter").value;
//  var filterdir = document.getElementById("filterdir").value;
  var max=document.getElementById("max").value;
  var scase=document.getElementById("scase").checked;
  //var dictionary=document.getElementById("dictionary").value;
  var seng = "true";
  outopt = document.getElementById("outopt").value;

  var accent = "";
  if (document.getElementById("accent")) {
    accent = document.getElementById("accent").value;
  }
  var swordhw = "";
  if (document.getElementById("swordhw")) {
    swordhw = document.getElementById("swordhw").value;
  }
  var url = "query.php" +
   "?word=" +encodeURIComponent(word) + 
   "&lastLnum=" + encodeURIComponent(lastLnum) +
   "&max=" +encodeURIComponent(max) +
   "&filter=" +encodeURIComponent(filter) +
   "&regexp=" + encodeURIComponent(regexp) +
   "&scase=" + encodeURIComponent(scase) +
   "&sword=" + encodeURIComponent(document.getElementById("sword").value) +
   "&sregexp=" + encodeURIComponent(document.getElementById("sregexp").value) +
   "&accent=" + encodeURIComponent(accent) +
   "&transLit=" + encodeURIComponent(document.getElementById("transLit").value) +
   "&outopt=" + encodeURIComponent(outopt) +
   "&swordhw=" + encodeURIComponent(swordhw);

    jQuery.ajax({
	url:url,
	type:"GET",
        success: function(data,textStatus,jqXHR) {
	    var mark = data.lastIndexOf("#");
	    lastLnum = data.substring(0,mark);
	    var databack = data.substring(mark+1);
	    if (lastLnum >= 0) {jQuery("#nextbtn").show();}
	    else {jQuery("#nextbtn").hide();}
	    jQuery("#disp").html(databack);
	    process_outopt4(databack);
	},
	error:function(jqXHR, textStatus, errorThrown) {
	    alert("Error: " + textStatus);
	}
    });
    
    jQuery("#disp").html="'<p>working...</p>'";
    jQuery("#data").html="";  
}

function getWord4(word) {
  var id = 'record_'+word;
  document.getElementById("data").scrollTop =
     document.getElementById(id).offsetTop;
}

function Hideworkbtn() {
  document.getElementById("workbtn").style.visibility="hidden";
  document.getElementById("workbtn").value='working...';
}

function process_outopt4(databack) {
 var reg = new RegExp("<!-- [^ ]+","g");
 var result;
 var result1="";
 var wlen,word;
 do
  {
  result=reg.exec(databack);
  if (result!=null) {
   result = result + ''; // convert to string
   result = result.substring(5);
   result1 = result1 + "<key1>" + result + "</key1>";
  }
  }
  while (result != null) {
  // next, gather all the data for result1
   gather(result1);
 }
}
function gather (data) {
  var filter="NONE";
  var utilchoice = "dump_key1";
  var accent = "";
  if (document.getElementById("accent")) {
    accent = document.getElementById("accent").value;
  }
  var url = "query_gather.php";
  var sendData = "data=" + encodeURIComponent(data)+
   "&utilchoice="+encodeURIComponent(utilchoice) +
   "&accent=" + encodeURIComponent(accent) +
   "&filter=" +encodeURIComponent(filter);

    jQuery.ajax({
	url:url,
	type:"POST",
	data:sendData,
        success: function(data,textStatus,jqXHR) {
	    //jQuery("#data").html(data);
	    displayDB(data);
	},
	error:function(jqXHR, textStatus, errorThrown) {
	    alert("Error: " + textStatus);
	}
    });
    jQuery("#data").html("<p>gathering data...");

}

function displayDB(data) {
  var filter = document.getElementById("filter").value;
  var url = "query_multi.php";
  // Change Mar 19, 2014
  // Change June 25, 2014 Is accent needed here?
  var accent = "";
  if (document.getElementById("accent")) {
   accent = document.getElementById("accent").value;
  }
  var sendData = "data=" +  encodeURIComponent(data) +
   "&accent=" + encodeURIComponent(accent) +
   "&filter=" +encodeURIComponent(filter);
    jQuery.ajax({
	url:url,
	type:"POST",
 	data:sendData,
        success: function(data,textStatus,jqXHR) {
            data = decodeURIComponent(data); // required to decode
	    jQuery("#data").html(data);
	    if (filter == 'deva') { // is this needed ?
                modifyDeva();
	    }
	},
	error:function(jqXHR, textStatus, errorThrown) {
	    alert("Error: " + textStatus);
	}
    });
    jQuery("#data").html("<p>preparing data display...");

}
function displayDBupdatePage() {
  if (request.readyState == 4) {
   requestActive=false;
   if (request.status == 200) {
    var response = request.responseText;
    var ansEl = document.getElementById("data"); 
    ansEl.innerHTML = response;
    return;
  } else {
    alert("Error! Request status is " + request.status);
  }
 } else {
//   alert("Note! Request readyState is " + request.readyState);
 }
}
function getFontClass() {
// June 25. Modify to always use siddhanta
 //var family = document.getElementById("devafont").value;
 var family = "siddhanta";
 if (family === "system") {return "sdata_system";}
 if (family === "praja") {return "sdata_praja";}
 if (family === "oldstandard") {return "sdata_oldstandard";}   
 if (family === "sanskrit2003") {return "sdata_sanskrit2003";}   
 if (family === "siddhanta") {return "sdata_siddhanta";}   
 return "sdata";
}
function modifyDeva() {
    var fontclass = getFontClass();
    var useragent = navigator.userAgent;
    if (!useragent) {useragent='';}
    if ((useragent.match(/Windows/i)) || (useragent.match(/Macintosh/i))){
  jQuery(".sdata").removeClass("sdata").addClass(fontclass);
 }else {
	//alert('useragent not "Windows"=' + useragent);
 }
}

function cookieUpdate(flag) {
 // 1. Cookie named 'mwiobasic' for holding transLit and filter values;
 // this cookie name is different from that used in the 'Preferences'
 // logic of webtc5(wb).
 // 2. The 'transLit' and 'filter' DOM elements are used to reset the cookie 
 // value when either (a) flag is TRUE, or (b) there is no old cookie value.
 // After the cookie value is set, then the cookie values are used to
 // set the DOM elements 'transLit', 'filter', 'input_input', 'input_output'.
 // 3. For the webtc logic (indexcaller.php), it is further desired to reset 
 // thecookie when there are parameters passed to the indexcaller.php program.
 // This is accomplished by checking the 'indexcaller' DOM element value; 
 // namely, when 'flag' is false and 'indexcaller' DOM element has value='YES',
 // then the cookie value is set as in 2, from the 'transLit' and 'filter' DOM
 // elements.

 var cookieName = 'mwiobasic';
 var cookieOptions = {expires: 365, path:'/'}; // 365 days
 var cookieValue = $.cookie(cookieName);
 var cookieValue_DOM = document.getElementById("transLit").value + "," + 
    document.getElementById("filter").value;

 if ((! flag) && (jQuery("#indexcaller").val() == "YES")) {
   // override cookie value
     cookieValue =  cookieValue_DOM;
 }else if ((! cookieValue) || flag) {
     cookieValue =  cookieValue_DOM;
 }
 $.cookie(cookieName,cookieValue,cookieOptions);
 // Now, make DOM elements consistent with cookieValue
 cookieValue = $.cookie(cookieName);
 var values = cookieValue.split(",");
 document.getElementById("transLit").value = values[0];
 document.getElementById("filter").value = values[1];
 document.getElementById("input_input").value = values[0];
 document.getElementById("input_output").value = values[1];
 //alert('cookie check2: ' + cookieValue);
};
$(document).ready(function() {
 // initialize handlers
  $('#word,#sword').keydown(function(event) {
   if (event.keyCode == '13') {
    event.preventDefault();
    getWord();
   }
   });
  $('#transLit,#filter').change(function(event) {
  cookieUpdate(true);   
  });
  // other initializations
  cookieUpdate(false);  // for initializing cookie
  lastLnum=0; // initialize several globals
  outopt="";
  jQuery("#disp").html=""; // blank the right display panel
  jQuery("#data").html=""; // blank the left display panel
  jQuery("#nextbtn").hide();
  jQuery("#workbtn").hide();
  // respond to RESTFUL requests
  var word=jQuery("#key").val();
  if (word) {getWord();}
});
