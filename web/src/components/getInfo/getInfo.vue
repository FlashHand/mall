<!-- 青菜  新增-->
<script>
var urlParam = getInfo.split_url('?');
var urlParam = getInfo.split_url('=');

export default {
        created() {
            if(urlParam){
                sessionStorage.setItem('user_ID', urlParam);
                    this.$router.push({
                        path : '/home'
                    });
            }

        },


    }
</script>



