-- Run in Supabase: SQL Editor -> New query -> Run
create table portfolios (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null default auth.uid() references auth.users(id) on delete cascade,
  full_name text not null,
  profile_picture_url text,
  email text, contact_number text, address text, about_me text,
  selected_template int not null default 1 check (selected_template in (1,2,3)),
  created_at timestamptz default now()
);
create table education (
  id uuid primary key default gen_random_uuid(),
  portfolio_id uuid not null references portfolios(id) on delete cascade,
  school text, degree text, year_start text, year_end text, sort_order int default 0
);
create table skills (
  id uuid primary key default gen_random_uuid(),
  portfolio_id uuid not null references portfolios(id) on delete cascade,
  skill_name text, sort_order int default 0
);
create table projects (
  id uuid primary key default gen_random_uuid(),
  portfolio_id uuid not null references portfolios(id) on delete cascade,
  title text, description text, link text, sort_order int default 0
);
create table experience (
  id uuid primary key default gen_random_uuid(),
  portfolio_id uuid not null references portfolios(id) on delete cascade,
  company text, position text, start_date text, end_date text, description text, sort_order int default 0
);
create table social_links (
  id uuid primary key default gen_random_uuid(),
  portfolio_id uuid not null references portfolios(id) on delete cascade,
  platform text, url text, sort_order int default 0
);

-- Row Level Security: each user only sees and changes their own data
alter table portfolios enable row level security;
create policy "own portfolios" on portfolios for all
  using (auth.uid() = user_id) with check (auth.uid() = user_id);

do $$
declare t text;
begin
  foreach t in array array['education','skills','projects','experience','social_links'] loop
    execute format('alter table %I enable row level security', t);
    execute format($p$create policy "own rows" on %I for all
      using (exists (select 1 from portfolios p where p.id = portfolio_id and p.user_id = auth.uid()))
      with check (exists (select 1 from portfolios p where p.id = portfolio_id and p.user_id = auth.uid()))$p$, t);
  end loop;
end $$;

-- Profile picture storage
insert into storage.buckets (id, name, public) values ('avatars', 'avatars', true) on conflict do nothing;
create policy "upload own avatars" on storage.objects for insert to authenticated
  with check (bucket_id = 'avatars' and (storage.foldername(name))[1] = auth.uid()::text);
