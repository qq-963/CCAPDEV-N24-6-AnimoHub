// theme, navbar, toast and other shared stuff
function applyTheme(){   
    var dark = localStorage.getItem("theme") == 'dark';
    if(dark) { document.body.classList.add("dark"); }
    else{ document.body.classList.remove('dark'); }
    var b = document.getElementById('themeBtn');
    if(b) { b.innerText = dark ? "Light mode" : 'Dark mode'; }
}

function toggleTheme() {
  var cur = localStorage.getItem("theme");
  localStorage.setItem('theme', cur == 'dark' ? "light" : "dark")
  applyTheme();
}
// escape text so it dont break the html
function esc(s){
  return String(s).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;").replace(/"/g,"&quot;");
}  

// toast notification
function toast(msg, type) {
    var box = document.getElementById("toasts");
    if (!box) {
        box = document.createElement("div");
        box.id = 'toasts';
         document.body.appendChild(box);
    }
     var t = document.createElement("div")
    t.className = "toast " + (type || "info");
    t.innerText = msg;
    box.appendChild(t)
    setTimeout(function (){
        t.classList.add('hide');
        setTimeout(function() { t.remove(); },400)
    }, 3000)
}


function openModal(id){ document.getElementById(id).classList.add("show"); }
function closeModal(id){ document.getElementById(id).classList.remove("show"); }

function toggleMenu() {
	document.querySelector('.nav').classList.toggle('open');
}

// simple bar chart
function drawBars(id, labels,values){
  var box = document.getElementById(id);
  box.innerHTML = '';
  var maxVal = Math.max.apply(null, values);
  if (maxVal <= 0) maxVal = 1;
  for(var i = 0; i < labels.length; i++) {
    var row = document.createElement("div");
    row.className = 'barrow'
    row.innerHTML = '<span class="barlabel">' + labels[i] + '</span><div class="bartrack"><div class="barfill" data-w="' + (values[i] / maxVal * 100) + '"></div></div><span class="barval">' + values[i] + '</span>';
    box.appendChild(row)
  }
  setTimeout(function() {
    var f = box.querySelectorAll('.barfill');
    for(var j = 0; j < f.length; j++){
      f[j].style.width = f[j].getAttribute('data-w') + '%'
    }
  }, 60);
}



// carousel
function makeCarousel(id){
    var c = document.getElementById(id);
    var slides = c.querySelectorAll(".slide")
    if(slides.length == 0) return
    var cur = 0;
    function show(n){
         cur = (n + slides.length) % slides.length;
        for(var i = 0; i < slides.length; i++){ slides[i].classList.remove("active"); }
        slides[cur].classList.add("active")
    }
    show(0);
    c.querySelector(".prev").onclick = function (){ show(cur - 1); };
    c.querySelector(".next").onclick = function() { show(cur + 1); };
    setInterval(function(){ show(cur + 1); }, 5000);
}
applyTheme();

// dropdown in the navbar (nav is made by php now)
var dd_btns = document.querySelectorAll(".dropbtn");
for(var i = 0; i < dd_btns.length; i += 1){
  dd_btns[i].onclick = function(e){
    e.preventDefault();
    this.parentNode.classList.toggle('open')
  }
}

// hide the toasts that php put in the page
setTimeout(function(){
  var t = document.querySelectorAll('#toasts .toast');
  for(var j = 0; j < t.length; j++){
    t[j].classList.add('hide');
    setTimeout(function(){ this.remove(); }.bind(t[j]), 400);
  }
}, 3000);
