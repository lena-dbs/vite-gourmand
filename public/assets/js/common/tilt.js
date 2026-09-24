/* TILT */
const ti = document.getElementById('tilt');
if(ti){
  ti.addEventListener('mousemove', function(e){
    const r=this.getBoundingClientRect();
    const xp=(e.clientX-r.left)/r.width-.5;
    const yp=(e.clientY-r.top)/r.height-.5;
    this.style.transition='transform .08s ease';
    this.style.transform=`perspective(900px) rotateY(${xp*9}deg) rotateX(${-yp*9}deg) scale(1.025)`;
  });
  ti.addEventListener('mouseleave', function(){
    this.style.transition='transform .6s ease';
    this.style.transform='perspective(900px) rotateY(0) rotateX(0) scale(1)';
  });
}
