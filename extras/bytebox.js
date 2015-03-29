var bytebox=function(){
	var b,i,im,src,co,st;
	return{
		init:function(){
			var ls = document.body.getElementsByTagName("a");for (i=0;i<ls.length;i++){if (ls[i].rel=='bytebox'){ls[i].onclick=bytebox.ld;}}
		},
		ld:function(){
			st=0;d=document;b=d.createElement('div'); d.body.appendChild(b);b.id='bb_ov';b.style.width=bytebox.wt()+"px";
			b.style.height=bytebox.ht()+"px";b.style.opacity=0.1;co=1; b.style.filter='alpha(opacity=01)';b.onclick=bytebox.r;
			src=this.href;setTimeout(bytebox.fd,1);return false;
		},
		fd:function(){
			if(co<=85&&st==0){co=co+20;b.style.opacity=co/100;b.style.filter='alpha(opacity='+co+')';setTimeout(bytebox.fd,0.5);}
			else if(st==0){im = new Image();im.onload=bytebox.l;im.src = src;}
			if(co<=100&&st==1){co=co+20;i.style.opacity=co/100;i.style.filter='alpha(opacity='+co+')';setTimeout(bytebox.fd,3);}


		},
		l:function(){
			d=document;ih=im.height;i=d.createElement('div');i.id='bb_div';
			tp=(((bytebox.h()/2)-((ih/2)+25))+bytebox.t());lp=((bytebox.w()/2)-((im.width/2)+20));
			if((tp+ih+60)>=bytebox.ht()){tp=bytebox.ht()-tp;tp=bytebox.ht()-(ih+60);

			if(tp<0){
				tp=0;
			}
			i.style.top=tp+"px";}
			else{
			if(tp<0){
				tp=0;
			}

			
			i.style.top=tp+"px";}
			i.style.left=lp+"px";
			n=d.createElement('img');n.src=im.src;i.appendChild(n);c=d.createElement('div');c.id='bb_da';cl=d.createElement('a');tn = document.createTextNode('CLOSE');
			cl.href="javascript:void(0)";cl.onclick=bytebox.r;cl.id="bb_a";
			d.body.appendChild(i);cl.appendChild(tn);c.appendChild(cl);i.appendChild(c);
if((80+ih)>bytebox.ht()){
	var nhh=ih+80;
				b.style.height=nhh+"px";
}
			i.style.opacity=0.1;co=1; i.style.filter='alpha(opacity=01)';st=1;setTimeout(bytebox.fd,1);
		},
		r:function(){st=2;document.body.removeChild(b);document.body.removeChild(i);},
		t:function(){return document.body.scrollTop||document.documentElement.scrollTop},
		w:function(){return self.innerWidth||(document.documentElement.clientWidth||document.body.clientWidth);},
		h:function(){return self.innerHeight||(document.documentElement.clientHeight||document.body.clientHeight);},
		wt:function(){return (document.body.scrollWidth||document.documentElement.scrollWidth)||(document.body.clientWidth||document.documentElement.clientWidth);},
		ht:function(){return (document.body.scrollHeight||document.documentElement.scrollHeight)||(document.body.clientHeight||document.documentElement.clientHeight);}
	}
}();