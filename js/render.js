const U = u => /^https?:\/\//i.test(u) ? u : 'https://' + u;
const ini = n => String(n || '?').split(/\s+/).filter(Boolean).slice(0, 2).map(w => w[0].toUpperCase()).join('');
const av = p => p.profile_picture_url ? `<img class="av" src="${esc(p.profile_picture_url)}" alt="Photo of ${esc(p.full_name)}">` : `<div class="av ph" aria-hidden="true">${esc(ini(p.full_name))}</div>`;
const contact = p => `<div class="contact">${[p.email, p.contact_number, p.address].filter(Boolean).map(x => `<span>${esc(x)}</span>`).join('')}</div>`;
const links = p => p.social_links.length ? `<div class="links">${p.social_links.map(l => `<a href="${esc(U(l.url))}" target="_blank" rel="noopener">${esc(l.platform || l.url)}</a>`).join('')}</div>` : '';
const dates = x => [x.start_date, x.end_date].filter(Boolean).join(' – ');
const edu = e => `<div class="it"><b>${esc(e.degree)}</b><span>${esc(e.school)}</span><em>${[e.year_start, e.year_end].filter(Boolean).join(' – ')}</em></div>`;
const exp = e => `<div class="it"><b>${esc(e.position)}</b><span>${esc(e.company)}</span><em>${dates(e)}</em><p>${esc(e.description)}</p></div>`;
const proj = x => `<div class="it"><b>${esc(x.title)}</b><p>${esc(x.description)}</p>${x.link ? `<a href="${esc(U(x.link))}" target="_blank" rel="noopener">View project</a>` : ''}</div>`;
const chip = s => `<span class="chip">${esc(s.skill_name)}</span>`;
const sec = (h, a, f, sc = '', ic = '') => a.length ? `<section class="${sc}"><h2>${h}</h2><div class="${ic}">${a.map(f).join('')}</div></section>` : '';
const about = (p, sc = '') => p.about_me ? `<section class="${sc}"><h2>About me</h2><p>${esc(p.about_me)}</p></section>` : '';

// Template 1: Simple, one centered column
const t1 = p => `<article class="t1"><header>${av(p)}<h1>${esc(p.full_name)}</h1>${contact(p)}${links(p)}</header>
${about(p)}${sec('Skills', p.skills, chip)}${sec('Experience', p.experience, exp)}${sec('Projects', p.projects, proj)}${sec('Education', p.education, edu)}</article>`;

// Template 2: Modern, hero banner + cards
const t2 = p => `<article class="t2"><div class="hero">${av(p)}<div><h1>${esc(p.full_name)}</h1>${contact(p)}${links(p)}</div></div>
<div class="grid">${about(p, 'card full')}${sec('Skills', p.skills, chip, 'card')}${sec('Education', p.education, edu, 'card')}
${sec('Experience', p.experience, exp, 'card full')}${sec('Projects', p.projects, proj, 'card full', 'projs')}</div></article>`;

// Template 3: Creative, side panel + timeline
const t3 = p => `<article class="t3"><aside>${av(p)}<h1>${esc(p.full_name)}</h1>${contact(p)}${links(p)}${p.skills.length ? `<h2>Skills</h2><div>${p.skills.map(chip).join('')}</div>` : ''}</aside>
<main>${about(p)}${sec('Experience', p.experience, exp, '', 'tl')}${sec('Projects', p.projects, proj, '', 'pj')}${sec('Education', p.education, edu, '', 'tl')}</main></article>`;

const render = (p, t) => [null, t1, t2, t3][t](p);
