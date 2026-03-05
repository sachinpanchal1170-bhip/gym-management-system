
const hamburger = document.getElementById('hamburger');
const menu = document.getElementById('menu');
hamburger?.addEventListener('click', () => {
  const open = menu.classList.toggle('open');
  hamburger.setAttribute('aria-expanded', open ? 'true' : 'false');
});


document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const id = a.getAttribute('href').slice(1);
    const el = document.getElementById(id);
    if (el) {
      e.preventDefault();
      window.scrollTo({ top: el.offsetTop - 70, behavior: 'smooth' });
      menu.classList.remove('open');
      hamburger.setAttribute('aria-expanded', 'false');
    }
  });
});


const modal = document.getElementById('joinModal');
const openers = document.querySelectorAll('[data-open-modal]');
const closers = document.querySelectorAll('[data-close-modal]');
openers.forEach(btn => btn.addEventListener('click', e => {
  e.preventDefault();
  modal.setAttribute('aria-hidden', 'false');
}));
closers.forEach(btn => btn.addEventListener('click', () => {
  modal.setAttribute('aria-hidden', 'true');
}));
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') modal.setAttribute('aria-hidden', 'true');
});
