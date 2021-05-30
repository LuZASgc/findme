//const baseURL = "https://findme.bibei365.com/api.php?";
const baseURL = "http://127.0.0.1:81/api.php?";
function NetRequest({ url, data, success, fail, complete, method = "POST" }) {

  var session_id = wx.getStorageSync('PHPSESSID');//本地取存储的sessionID  
  console.log("savded:---------" + session_id);
  if (session_id != "" && session_id != null) {
    var header = { 'content-type': 'application/x-www-form-urlencoded', 'Cookie': 'PHPSESSID=' + session_id }
  } else {
    var header = { 'content-type': 'application/x-www-form-urlencoded' }
  }

  console.log(session_id);
  url = baseURL + url;
  wx.request({
    url: url,
    method: method,
    data: data,
    header: header,
    success: res => {  
      if (session_id == "" || session_id == null) {
        console.log("got:--" + res.data.session_id);
        wx.setStorageSync('PHPSESSID', res.data.session_id) //如果本地没有就说明第一次请求 把返回的session id 存入本地  
      }
      console.log(res);
      let data = res.data
      res['statusCode'] === 200 ? success(data) : fail(res)
    },
    fail: fail,
    complete: complete
  })

}  


module.exports.request = NetRequest
module.exports.baseURL = baseURL
