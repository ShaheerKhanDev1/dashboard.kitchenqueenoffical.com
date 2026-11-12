(() => {
  if (!KQApi.token()) return;
  const esc=KQApp.esc;
  const dismissed=new Map();
  let busy=false, current=null, lastFocus=null, failures=0;
  const dialog=document.createElement('dialog');dialog.className='payment-dialog';
  dialog.setAttribute('aria-labelledby','paymentTitle');document.body.append(dialog);
  dialog.addEventListener('close',()=>{if(current)dismissed.set(current.id,Date.now());current=null;lastFocus?.focus();});
  async function show(order){
    current=order;lastFocus=document.activeElement;
    dialog.innerHTML=`<div class="payment-head"><span class="eyebrow">PAYMENT REVIEW</span><button type="button" class="btn btn-ghost" data-later aria-label="Close payment review">×</button></div><h2 id="paymentTitle">EasyPaisa approval required</h2><p>${esc(order.order_no)} · ${esc(order.customer_name)}</p><p class="payment-amount">${KQApp.fmtPKR(order.total)}</p><p>Check the transaction in your EasyPaisa account and compare the screenshot before approving.</p><div class="payment-proof"><img id="paymentProof" alt="Customer payment screenshot"></div><p id="paymentError" role="alert"></p><div class="payment-buttons"><button class="btn btn-gold" data-decision="approve">Approve payment</button><button class="btn btn-red" data-decision="reject">Reject payment</button><button class="btn btn-ghost" data-later>Review later</button></div>`;
    dialog.querySelectorAll('[data-later]').forEach(b=>b.onclick=()=>dialog.close());
    dialog.querySelectorAll('[data-decision]').forEach(b=>b.onclick=async()=>{
      dialog.querySelectorAll('button').forEach(x=>x.disabled=true);
      try {await KQApi.decidePayment(order.id,b.dataset.decision);dialog.close();KQApp.toast('Payment '+(b.dataset.decision==='approve'?'approved':'rejected'));document.dispatchEvent(new Event('kq:payment'));}
      catch(e){dialog.querySelector('#paymentError').textContent=e.message;}
      finally{dialog.querySelectorAll('button').forEach(x=>x.disabled=false);}
    });
    dialog.showModal();
    try {const url=await KQApi.slip(order.id);const img=dialog.querySelector('#paymentProof');if(current?.id===order.id){img.onload=()=>URL.revokeObjectURL(url);img.src=url;}else URL.revokeObjectURL(url);}
    catch(e){dialog.querySelector('#paymentError').textContent=e.message;}
  }
  async function poll(){
    if(busy || document.hidden) return;
    busy=true;
    try {
      const res=await KQApi.notifications();failures=0;
      const orders=res.data.orders || [];
      const count=document.querySelector('#paymentCount b');if(count)count.textContent=res.data.count;
      const next=orders.find(o=>Date.now()-(dismissed.get(o.id)||0)>300000);
      if(current && !orders.some(o=>o.id===current.id)) dialog.close();
      if(next && !dialog.open) await show(next);
    } catch(e){failures++;if(failures===3)KQApp.toast('Live payment alerts disconnected. Open Orders to refresh.');}
    finally{busy=false;}
  }
  setInterval(poll,1000);poll();
})();
