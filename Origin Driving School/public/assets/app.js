
document.addEventListener('click', e=>{
  const t = e.target.closest('[data-navtoggle]');
  if(!t) return;
  document.getElementById('mainNav')?.classList.toggle('open');
});

const password = document.querySelector('#password');
const meter = document.querySelector('#pwMeter');
if(password && meter){
  password.addEventListener('input', ()=>{
    const v = password.value;
    let score = 0;
    if(v.length >= 8) score++;
    if(/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
    if(/\d/.test(v)) score++;
    if(/[^A-Za-z0-9]/.test(v)) score++;
    meter.textContent = ['Weak','Okay','Good','Strong','Legendary'][score] || 'Weak';
  });
}
