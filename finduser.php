<?php
$pageTitle='Customers · Kitchen Queen';$bodyTitle='Customers';$assetPrefix='';
require __DIR__.'/includes/header.php';
?>
<div id="page"><div class="section-title"><div><p class="eyebrow">CUSTOMER DIRECTORY</p><h3>People ordering from your kitchen</h3></div><span id="customerCount" class="badge">Loading…</span></div><div class="card"><div class="searchbar"><input id="customerSearch" aria-label="Search customers" placeholder="Search name, email or phone"><select id="customerState" aria-label="Account status"><option value="">All accounts</option><option value="1">Active</option><option value="0">Disabled</option></select></div><p id="customerError" role="alert"></p><div class="table-wrap"><table class="data"><thead><tr><th>Customer</th><th>Contact</th><th>Joined</th><th>Last sign-in</th><th>Status</th><th>Action</th></tr></thead><tbody id="customers"></tbody></table></div><p class="muted">Latest 500 registered accounts. Disabling an account prevents sign-in and invalidates access to customer APIs.</p></div></div>
<script>
document.addEventListener('DOMContentLoaded',()=>{
 let rows=[];const esc=KQApp.esc;
 function render(){const q=document.getElementById('customerSearch').value.toLowerCase(),st=document.getElementById('customerState').value;const list=rows.filter(r=>(st===''||String(r.is_active)===st)&&[r.name,r.email,r.phone].join(' ').toLowerCase().includes(q));document.getElementById('customerCount').textContent=list.length+' customers';document.getElementById('customers').innerHTML=list.map(r=>`<tr><td><strong>${esc(r.name)}</strong></td><td>${esc(r.email)}<br><small>${esc(r.phone)}</small></td><td>${esc(r.created_at)}</td><td>${esc(r.last_login_at||'Never')}</td><td><span class="badge ${Number(r.is_active)?'b-done':'b-cancel'}">${Number(r.is_active)?'Active':'Disabled'}</span></td><td><button class="btn btn-ghost btn-sm" data-id="${Number(r.id)}">${Number(r.is_active)?'Disable':'Enable'}</button></td></tr>`).join('')||'<tr><td colspan="6">No matching customers.</td></tr>';}
 async function load(){try{rows=(await KQApi.customers()).data.users;render();}catch(e){document.getElementById('customerError').textContent=e.message;}}
 document.getElementById('customerSearch').oninput=render;document.getElementById('customerState').onchange=render;
 document.getElementById('customers').onclick=async e=>{const b=e.target.closest('[data-id]');if(!b)return;const r=rows.find(r=>Number(r.id)===Number(b.dataset.id));if(!confirm((Number(r.is_active)?'Disable':'Enable')+' account for '+r.name+'?'))return;b.disabled=true;try{await KQApi.setCustomer(r.id,Number(r.is_active)?0:1);await load();}catch(e){KQApp.toast(e.message);b.disabled=false;}};load();
});
</script>
<?php require __DIR__.'/includes/footer.php'; ?>
</body></html>
