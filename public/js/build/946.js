"use strict";(self.webpackChunkDooTask=self.webpackChunkDooTask||[]).push([[946],{55983:(t,s,i)=>{i.d(s,{Z:()=>o});var e=i(1519),a=i.n(e)()((function(t){return t[1]}));a.push([t.id,".valid-wrap{align-items:center;display:flex;height:100vh;justify-content:center;width:100vw}.valid-box{background-color:#fff;border-radius:10px;padding:5px 15px 20px;width:500px}.valid-box .valid-title{border-bottom:1px solid #e8eaec;line-height:1;padding:14px 16px}.valid-box .validation-text{color:#333;font-size:14px;padding:10px}",""]);const o=a},73946:(t,s,i)=>{i.r(s),i.d(s,{default:()=>c});const e={name:"validEmail",components:{},data:function(){return{success:!1,error:!1}},mounted:function(){this.verificationEmail()},watch:{},methods:{verificationEmail:function(){var t=this;this.$store.dispatch("call",{url:"users/email/verification",data:{code:this.$route.query.code}}).then((function(s){s.data;t.success=!0,t.error=!1})).catch((function(s){var i=s.data;s.msg;2===i.code?t.goForward({path:"/"},!0):(t.success=!1,t.error=!0)}))},userLogout:function(){this.$store.dispatch("logout",!1)}}};var a=i(93379),o=i.n(a),r=i(55983),n={insert:"head",singleton:!1};o()(r.Z,n);r.Z.locals;const c=(0,i(51900).Z)(e,(function(){var t=this,s=t.$createElement,i=t._self._c||s;return i("div",{staticClass:"valid-wrap"},[i("div",{staticClass:"valid-box"},[i("div",{staticClass:"valid-title"},[t._v(t._s(t.$L("验证邮箱")))]),t._v(" "),t.success||t.error?t._e():i("Spin",{attrs:{size:"large"}}),t._v(" "),t.success?i("div",{staticClass:"validation-text"},[i("p",[t._v(t._s(t.$L("您的邮箱已通过验证")))]),t._v(" "),i("p",[t._v(t._s(t.$L("今后您可以通过此邮箱重置您的账号密码")))])]):t._e(),t._v(" "),t.error?i("div",{staticClass:"validation-text"},[i("div",[t._v(t._s(t.$L("链接已过期，请重新发送")))])]):t._e(),t._v(" "),t.success?i("div",{attrs:{slot:"footer"},slot:"footer"},[i("Button",{attrs:{type:"primary",long:""},on:{click:t.userLogout}},[t._v(t._s(t.$L("返回首页")))])],1):t._e()],1)])}),[],!1,null,null,null).exports}}]);