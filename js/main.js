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
// returns the logged in user
function getUser() {
  var em = localStorage.getItem('currentUser')
  if(!em) return null;
  return getUserByEmail(em);
}

function checkLogin(){
	var u = getUser();
	if(u==null) { window.location.href = "index.html"; }
	return u;
}

function logout(){
	localStorage.removeItem("currentUser");
	window.location.href = 'index.html'
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

// builds the navbar
function buildNav() {
  var navBox = document.getElementById("navbar");
  var u = getUser()
  if(!navBox || !u) return
  var page = location.pathname.split('/').pop()||"index.html";

  function lk(href, text) {
    return '<a href="' + href + '"' + (page == href ? ' class="active"' : "")+">" + text+"</a>"
  }

  var h='<div class="nav">';
  h += '<button id="menuBtn" onclick="toggleMenu()">&#9776;</button>';
  h += '<a class="logo" href="dashboard.html">AnimoHub</a>';
  h += '<div class="links">';
  h += lk("dashboard.html", "Dashboard");
  if(u.role == "student"){
    h += '<div class="dropdown"><a href="#" class="dropbtn">Enlistment &#9662;</a>';
    h += '<div class="dropcontent">' + lk("enlistment.html", 'My Enlistment') + lk("courses.html", "Course Offerings") + "</div></div>";
  }else {
    h += lk('courses.html', "Course Offerings")
  }
  h += lk("organizations.html", "Organizations");
  if (managedOrgs(u.email).length>0) h += lk("orgsettings.html", "Org Settings");
  if (u.role == "admin") h += lk("admin.html", 'Admin');
  h += lk("profile.html", "Profile");
   h += '</div>';
  h += '<div class="right"><span class="who">' + esc(u.name) + '</span>';
  h += '<button id="themeBtn" onclick="toggleTheme()">Dark mode</button>';
  h += '<button onclick="logout()">Logout</button></div>';
  h += '</div>';
  navBox.innerHTML = h

  var dd_btns = document.querySelectorAll(".dropbtn");
  for(var i = 0; i < dd_btns.length; i += 1){
    dd_btns[i].onclick = function(e){
      e.preventDefault();
      this.parentNode.classList.toggle('open')
    }
  }
  applyTheme();
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
buildNav()
