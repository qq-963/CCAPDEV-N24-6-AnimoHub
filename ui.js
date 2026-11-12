// theme, toast and other shared ui stuff (phase 2 pages)
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

// fade out a toast after 3 sec
function hideToast(t) {
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

// toasts that php put in the page
var serverToasts = document.querySelectorAll('#toasts .toast');
for(var k = 0; k < serverToasts.length; k++){ hideToast(serverToasts[k]); }

// navbar dropdown
var dd_btns = document.querySelectorAll(".dropbtn");
for(var i = 0; i < dd_btns.length; i += 1){
  dd_btns[i].onclick = function(e){
    e.preventDefault();
    this.parentNode.classList.toggle('open')
  }
}

// bars from php grow after load
setTimeout(function() {
  var f = document.querySelectorAll('.barfill');
  for(var j = 0; j < f.length; j++){
    f[j].style.width = f[j].getAttribute('data-w') + '%'
  }
}, 60);

if(document.getElementById('carousel')) makeCarousel('carousel');
