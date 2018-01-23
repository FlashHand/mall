<template>
    <div class="edit-wrap">
        <div v-title :data-title="title">{{title}}</div>
        <edit-header :text="title" :btn="btn" :id="$route.params.status"></edit-header>
        <div class="form-wrap">
            <mt-field label="收货人：" placeholder="请输入收货人姓名" v-model="data.realname"></mt-field>
            <mt-field label="手机号：" placeholder="请输入手机号" type="number" v-model="data.mobile"></mt-field>
            <div class="mint-cell mint-field">
                <span class="mint-cell-title mint-cell-text">所在地区：</span>
                <div class="mint-cell-value" @click="callingArea">
                    <input placeholder="请选择地区" type="tel" class="mint-field-core" disabled v-model="data.addressAll">
                    <span class="mint-field-state is-default"><i class="mintui mintui-field-default"></i></span>
                    <div class="mint-field-other"></div>
                </div>
            </div>
            <mt-field label="详细地址：" placeholder="请输入详细地址" v-model="data.address"></mt-field>
            <mt-radio
                align="right"
                v-model="value"
                :options="['设为默认地址']">
            </mt-radio>
            <mt-popup v-model="popupVisible" position="bottom" class="mint-popup-4">  
                <div class="picker-toolbar">  
                    <span class="mint-datetime-action mint-datetime-cancel" @click="cancleaddress">取消</span>  
                    <span class="mint-datetime-action mint-datetime-confirm" @click="selectaddress">确定</span>  
                </div>  
                <div class="select">
                    <select v-model="prov">
                        <option v-for="option in addressPlace" :value="option" :key="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                    <select v-model="city" v-if="city">
                        <option v-for="option in cityArr" :value="option" :key="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                    <select v-model="district" v-if="district">
                        <option v-for="option in districtArr" :value="option" :key="option.id">
                            {{ option.name }}
                        </option>
                    </select>
                </div>
            </mt-popup>
        </div>
        <footer-btn :text="footBnt" :id="$route.params.status" :to="'/address1'" :data="data" :prov="prov" :city="city" :district="district" :value="value"></footer-btn>
    </div>
</template>
<script>
    import EditHeader from '@/components/page/children/header';
    import FooterBtn from '@/components/Widget/fixedBtn';
    import { Field,Popup,Radio,Toast } from 'mint-ui';
    import qs from 'qs';
    export default {
        name : 'EditDelAddress',
        data(){
            return {
                title : '新增收货地址',
                btn:' ',
                btnDel:true,
                footBnt:'保存',
                popupVisible:false,
                popup:false,
                value:'',
                addressPlace:'',
                prov:'',
                cityArr:'',
                city:'',
                district:'',
                districtArr:'',
                data:{
                    name:'',
                    phone:'',
                    address1:'',
                    addressAll:'',
                    addAll:'',
                    Det:'',
                    addressa:'',
                    value:''
                }
            }
        },
        methods:{
            callingArea(){
                this.popupVisible = true;
            },
            cancleaddress(){
                this.popupVisible = false;
            },
            callingadd(){
                this.popup = true;
            },
            selectaddress(){
                this.popupVisible = false;
                if(this.prov.name != undefined && this.city.name != undefined){
                    this.data.addressAll = this.prov.name +'-'+ this.city.name +'-'+ this.district.name;
                }else{
                    Toast('请选择地址');
                }
            },
            beforeMount: function () {
                this.updateCity();
                this.updateDistrict();
            },
            updateCity: function () {
                for (var i in this.addressPlace) {
                    var obj = this.addressPlace[i];
                    if (obj == this.prov) {
                        this.cityArr = obj.son;
                        break;
                    }
                }
                this.city = this.cityArr[1];
            },
            updateDistrict: function () {
                for (var i in this.cityArr) {
                    var obj = this.cityArr[i];
                    if (obj == this.city) {
                        this.districtArr = obj.grandson;
                        break;
                    }
                }
                if(this.districtArr && this.districtArr.length > 0 && this.districtArr[1]) {
                    this.district = this.districtArr[1];
                } else {
                    this.district = '';
                }
            }
        },
        watch: {
            prov () {
                this.updateCity();
                this.updateDistrict();
            },
            city () {
                this.updateDistrict();
            },
            value(){
                if(this.value == '设为默认地址'){
                    this.data.value = '1';
                }
            }
        },
        created(){
            
        },
        mounted(){
            this.axios.post(API_URL + 'Home/Pcenter/addressPlace',qs.stringify({
                app_user_id:sessionStorage.getItem('user_ID')
            })).then((res) => {
                this.addressPlace = res.data.data;
            }).catch((err) => {
                console.log(err);
            });
        },
        components:{
            EditHeader,
            FooterBtn
        }
    }
</script>
<style lang="less">
    .edit-wrap{
        .mint-popup-bottom{
            width: 100%;
            padding:.2rem;
            box-sizing: border-box;
            .select{
                padding:.2rem 0;
                select{
                    width: 30%;
                    height: .5rem;
                    background:#fff;
                    outline: none;
                    option{
                        color:#333;
                        text-align: center;
                    }
                }
            }
        }
        div.mint-field{
            border-top:1px solid #d9d9d9;
            padding:0 .2rem;
        }
        .mint-radiolist-title{
            margin:0;
        }
        .mint-radiolist-label{
            padding:0;
        }
        .mint-radio-label{
            font-size:.29rem;
            color:#999;
        }
        .mint-field{
            height: 1rem;
            line-height:1rem;
            background:#fff;
            .mint-cell-text{
                font-size:.3rem;
                color:#999;
            }
            .mint-cell-value{
                font-size:.32rem;
                color:#333;
                input{
                    background:#fff;
                }
            }
        }
    }
</style>
<style lang="less" scoped>
    
</style>