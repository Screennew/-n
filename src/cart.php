<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
require_once __DIR__.'/../config/db.php';

function cart_get(){ $_SESSION['cart']=$_SESSION['cart']??['items'=>[],'coupon'=>null]; return $_SESSION['cart']; }
function cart_add($pid,$qty=1){ $c=cart_get(); $c['items'][$pid]=($c['items'][$pid]??0)+max(1,(int)$qty); $_SESSION['cart']=$c; }
function cart_set($pid,$qty){ $c=cart_get(); if($qty<=0) unset($c['items'][$pid]); else $c['items'][$pid]=(int)$qty; $_SESSION['cart']=$c; }
function cart_clear(){ $_SESSION['cart']=['items'=>[],'coupon'=>null]; }

function cart_items_detailed(){
  $c=cart_get(); if(!$c['items']) return [];
  $ids=implode(',', array_map('intval', array_keys($c['items'])));
  $rows=db()->query("SELECT id,name,price,image_url FROM products WHERE id IN ($ids)")->fetchAll();
  $out=[]; foreach($rows as $r){ $q=$c['items'][$r['id']]??0; $out[]=['id'=>$r['id'],'name'=>$r['name'],'price'=>$r['price'],'image_url'=>$r['image_url'],'qty'=>$q,'total'=>$q*$r['price']]; }
  return $out;
}
function cart_totals(){
  $sub=0; foreach(cart_items_detailed() as $it) $sub+=$it['total'];
  $discount=0; $coupon=cart_get()['coupon']??null;
  if($coupon){ if($coupon['type']==='percent') $discount=$sub*($coupon['value']/100.0); else $discount=min($coupon['value'],$sub); }
  $ship=$sub>0?15000:0; $total=max(0,$sub-$discount+$ship);
  return ['subtotal'=>$sub,'discount'=>$discount,'shipping'=>$ship,'total'=>$total];
}
function apply_coupon($code){
  $st=db()->prepare('SELECT * FROM coupons WHERE code=? AND active=1 AND (expires_at IS NULL OR expires_at>=NOW()) LIMIT 1');
  $st->execute([$code]); $c=$st->fetch();
  if($c){ $_SESSION['cart']['coupon']=['code'=>$c['code'],'type'=>$c['type'],'value'=>$c['value']]; return true; }
  return false;
}
