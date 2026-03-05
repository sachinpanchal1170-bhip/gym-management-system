document.querySelectorAll('[data-eye]').forEach(el=>{
  el.addEventListener('click', ()=>{
    const input = el.previousElementSibling;
    input.type = input.type === 'password' ? 'text' : 'password';
    el.textContent = input.type === 'password' ? 'Show' : 'Hide';
  });
});

document.querySelectorAll('form[data-nosubmit-twice]').forEach(f=>{
  f.addEventListener('submit',()=>{
    const btn = f.querySelector('button[type="submit"]');
    if(btn){ btn.disabled = true; btn.textContent = 'Please wait…'; }
  });
});

const inputs = document.querySelectorAll("input");

inputs.forEach(input => {
  input.addEventListener("focus", () => {
    input.style.boxShadow = "0 0 10px #FFD700"; 
  });
  input.addEventListener("blur", () => {
    input.style.boxShadow = "none";
  });
});
