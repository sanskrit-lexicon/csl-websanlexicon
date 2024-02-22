// web/webtc2/main.js
// Mar 29, 2014.  Changed escape to encodeURIComponent, and
// Aug 6, 2020. query_gather and query_multi replaced by query_gather1
//  JS additions: process_outopt5, gather1
//  JS deletions: process_outopt4, displayDB , displayDBupdatePage.
//     gather, 
// Jan 10, 2024  highlighting.
//   Based on code from Anatoly Artemenko
//   Ref: https://github.com/sanskrit-lexicon/COLOGNE/issues/5#issuecomment-1884373054
// Jan 26, 2024  Revised highlighting per problem noted in
// https://github.com/sanskrit-lexicon/MWS/issues/160
var nextButton,prevButton,nearestButton,currentIndex;  // globals for highlight
function init_highlight_globals() {
    currentIndex = -1;
    // console.log('nextButton', nextButton == undefined); //true 1st time
    if (nextButton == undefined) {
     // initialize
    nextButton = document.getElementById("nextButton");
    prevButton = document.getElementById("prevButton");
    nearestButton = document.getElementById("nearestButton");

    nextButton.addEventListener("click", function() {
      navigateToNextHighlight();
    });

    nearestButton.addEventListener("click", function() {
      activateNearestVisibleHighlight();
    });

    prevButton.addEventListener("click", function() {
      navigateToPreviousHighlight();
    });
    } // end of initialization
}
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
         let json = JSON.parse(data);
         lastLnum = json['lastlnum'];
	 if (lastLnum >= 0) {jQuery("#nextbtn").show();}
	 else {jQuery("#nextbtn").hide();}
         let keydata = json['data'];
         let filter = json['filter'];
         let html = getNext_html(keydata,filter);
         jQuery("#disp").html(html);
	let hword;
	if (word != ''){
 	 hword = word;
	}else {
	 hword = sword;
	}
         process_outopt5(keydata,hword);
	},
	error:function(jqXHR, textStatus, errorThrown) {
	    alert("Error: " + textStatus);
	}
    });
    
    jQuery("#disp").html("<p>working...</p>");
    jQuery("#data").html("");
}
function getNext_html(keydata,filter) {
 if (keydata.length == 0) {
  return "<p>No matches found</p>";
 }
 let htmlarr = [];
 //htmlarr.push("<p class='words'>");
 //let c = 'words';
 htmlarr.push("<p class='words'>");
 let c = 'sdata';
 if (filter == 'deva') {
  c = 'sdata_siddhanta';
 }
 for(let i=0;i<keydata.length;i++) {
  let rec = keydata[i];
  let nx = i+1;
  let key = rec.key;
  let keyout = rec.keyout;
  let matchword = rec.matchword
  if (matchword != ''){
   matchword = ` (${matchword})`;
  }
  let x = `${nx} <!-- ${key} --><a class='${c}' onclick='getWord4(\"${nx}\");'>${keyout}</a>${matchword}<br>`;
  htmlarr.push(x);
 }
 htmlarr.push("</p>");
 html = htmlarr.join("\n");
 return html;
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

function process_outopt5(keydata,hword) {
 if (keydata.length == 0) {return;}
 let resultarr = [];
 for(let i=0;i<keydata.length;i++) {
  let rec = keydata[i];
  let key = rec.key;
  resultarr.push(key);
 }
 gather1(resultarr,hword); 
}

function gather1 (keys,hword) {
  var filter = document.getElementById("filter").value;

  var accent = "";
  if (document.getElementById("accent")) {
    accent = document.getElementById("accent").value;
  }
  let json = JSON.stringify(keys);
  var url = "query_gather1.php";
  var sendData = "data=" + encodeURIComponent(json)+
   "&accent=" + encodeURIComponent(accent) +
   "&filter=" +encodeURIComponent(filter);

    jQuery.ajax({
	url:url,
	type:"POST",
	data:sendData,
        success: function(data,textStatus,jqXHR) {
            jQuery("#data").html(data);
            let elt = document.getElementById("data");
	    let searchTerm = hword; // The text you are looking for
	    let divContent = elt.innerHTML;
            //let highlightedContent = divContent.replace(new RegExp(searchTerm,'gi'), '<span class="highlight">$&</span>'); // artanat method
	    // next avoids highlighting within tags
	    let highlightedContent = replaceText_highlight(divContent, searchTerm);
	    //console.log('divContent=',divContent);
	    //console.log('highlightedContent=',highlightedContent);
            elt.innerHTML = highlightedContent;
	    init_highlight_globals();
	},
	error:function(jqXHR, textStatus, errorThrown) {
	    alert("Error: " + textStatus);
	}
    });
    jQuery("#data").html("<p>gathering data...");

}
function replaceText_highlight(str, old) {
    const flag = document.getElementById("as_highlight").value;
    if (flag === 'yes') {
	return str.replace(new RegExp(`(?!<[^>]*)(${old})(?![^<]*>)`, 'g'), match => `<span class="highlight">${match}</span>`);
    }else {
	return str;
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
 // 02-21-2024. 
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
   // as_highlight_changeF();
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
// Functions used in highlighting, See init_highlight_globals above.
    function navigateToPreviousHighlight() {
      const highlightElements = document.querySelectorAll(".highlight");

      if (currentIndex !== -1) {
        highlightElements[currentIndex].classList.remove("active");
      }

      currentIndex = (currentIndex - 1 + highlightElements.length) % highlightElements.length;
      highlightElements[currentIndex].classList.add("active");
      highlightElements[currentIndex].scrollIntoView({ behavior: "smooth" });
    }

    function navigateToNextHighlight() {
      const highlightElements = document.querySelectorAll(".highlight");

      if (currentIndex !== -1) {
        highlightElements[currentIndex].classList.remove("active");
      }

      currentIndex = (currentIndex + 1) % highlightElements.length;
      highlightElements[currentIndex].classList.add("active");
      highlightElements[currentIndex].scrollIntoView({ behavior: "smooth" });
    }

    function activateNearestVisibleHighlight() {
      const highlightElements = document.querySelectorAll(".highlight");

      highlightElements.forEach(element => element.classList.remove("active"));

      let minDistance = Number.MAX_SAFE_INTEGER;
      let nearestIndex = -1;

      highlightElements.forEach((element, index) => {
        const rect = element.getBoundingClientRect();
        const distance = Math.abs(rect.top);

        if (distance < minDistance) {
          minDistance = distance;
          nearestIndex = index;
        }
      });

      if (nearestIndex !== -1) {
        currentIndex = nearestIndex;
        highlightElements[currentIndex].classList.add("active");
        highlightElements[currentIndex].scrollIntoView({ behavior: "smooth" });
      }
    }
function unused_unhighlight() {
    const highlightElements = document.querySelectorAll(".highlight");
    // Loop through the collection and remove the class
    highlightElements.forEach(element => {
	element.classList.remove('highlight')
    });
}
function as_highlight_changeF() {
    let hlval = document.getElementById("as_highlight").value;
    let buttondiv = document.getElementById("button-container");
    let dispdiv = document.getElementById("disp"); // left list
    let datadiv = document.getElementById("data"); // right list
    
    //console.log('as_highlight_changeF: hlval=',hlval);
    dispdiv.innerHTML = "";
    if (hlval == 'yes') {
	datadiv.innerHTML = "Next search will use highlighting ";
	buttondiv.style.display = "block";
    } else {
        datadiv.innerHTML = "Next search will not use highlighting ";
	buttondiv.style.display = "none";
    }
}
