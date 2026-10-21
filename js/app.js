// Paste your Project URL and anon/public key (Supabase > Project Settings > API).
// NEVER paste the service_role key here.
const SUPABASE_URL = 'https://lisnucuasxsuroxvdvmv.supabase.co';
const SUPABASE_ANON_KEY = 'sb_publishable_5CyPhrckiRVgI0zHZhiPEw_I7JElk-g';
const sb = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);

const $ = (s, r = document) => r.querySelector(s);
const qs = new URLSearchParams(location.search);
const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const TNAMES = ['', 'Simple', 'Modern', 'Creative'];
// child tables and their fields
const G = {
  education: ['school','degree','year_start','year_end'],
  skills: ['skill_name'],
  projects: ['title','description','link'],
  experience: ['company','position','start_date','end_date','description'],
  social_links: ['platform','url']
};

async function session() { return (await sb.auth.getSession()).data.session; }
function nav(s) {
  $('#nav').innerHTML = s
    ? '<a href="manage.html">My portfolios</a><a href="form.html">Create portfolio</a><button id="lo">Log out</button>'
    : '<a href="login.html">Log in</a>';
  if (s) $('#lo').onclick = async () => { await sb.auth.signOut(); location.href = 'index.html'; };
}
async function requireAuth() {
  const s = await session(); nav(s);
  if (!s) { location.href = 'login.html?next=' + encodeURIComponent(location.pathname.split('/').pop() + location.search); return null; }
  return s;
}
async function getPortfolio(id) {
  const { data, error } = await sb.from('portfolios')
    .select('*, education(*), skills(*), projects(*), experience(*), social_links(*)').eq('id', id).single();
  if (error) throw error;
  for (const t in G) data[t].sort((a, b) => a.sort_order - b.sort_order);
  return data;
}
async function savePortfolio(base, kids, id) {
  let pid = id;
  if (id) {
    const { error } = await sb.from('portfolios').update(base).eq('id', id); if (error) throw error;
    for (const t in G) { const { error: e } = await sb.from(t).delete().eq('portfolio_id', id); if (e) throw e; }
  } else {
    const { data, error } = await sb.from('portfolios').insert(base).select().single(); if (error) throw error;
    pid = data.id;
  }
  for (const t in kids) if (kids[t].length) {
    const { error } = await sb.from(t).insert(kids[t].map((r, i) => ({ ...r, portfolio_id: pid, sort_order: i })));
    if (error) throw error;
  }
  return pid;
}
