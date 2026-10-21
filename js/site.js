// Shared menu (loads after app.js and replaces its simple menu)
nav = function (s) {
  const cur = location.pathname.split('/').pop() || 'index.html';
  const L = (h, t) => `<a href="${h}"${cur === h ? ' aria-current="page"' : ''}>${t}</a>`;
  $('#nav').innerHTML = L('index.html', 'Home') + L('templates.html', 'Templates') + L('features.html', 'Features') +
    (s ? L('manage.html', 'My portfolios') + L('form.html', 'Create portfolio') + '<button id="lo">Log out</button>' : L('login.html', 'Log in'));
  if (s) $('#lo').onclick = async () => { await sb.auth.signOut(); location.href = 'index.html'; };
};
