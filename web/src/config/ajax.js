const ajax = function(type, url, data, fn) {
    var datas;
    if (type == 'get') {
        datas = {
            params: data
        };
    } else {
        datas = data;
    }
    this.axios[type](url, datas).then((res) => {
        console.info('SuccessRes', res);
        if (!res.data.error) {
            fn && fn(res.data);
        } else {
            cbs.statusError.call(this, res.data.error);
        }
    }).catch((err) => {
        console.info('FailtrueErr', err);
        cbs.requestError.call(this.err);
    });
}
export default ajax;