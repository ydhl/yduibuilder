;

/**
 * 渲染文件上传， 针对vue的版本
 * @returns {undefined}
 */
// eslint-disable-next-line camelcase
function yd_upload_render(el, url, mine, file_add_cb, file_uploaded_cb, file_complete_cb) {
  var render = function (el) {
    if ($(el).attr("yd-component") ) return;

    $(el).attr("yd-component","upload");

    var file_err_cb = $(el).attr("data-error-callback");
    var file_progrerss_cb = $(el).attr("data-progress-callback");
    var multi_selection =  $(el).attr("data-multi-selection")||0;
    var file_name = $(el).attr("data-file-name") || 'file';
    var auto_upload = $(el).attr("data-auto-upload") || "1";

    var mine_types = mine;
    if (! /\*|\//.test(mine)){
        mine_types = [{ title : mine, extensions : mine }];
    }
    var jwt = window.sessionStorage.getItem('jwt');
    var uploader = new plupload.Uploader({
        browse_button: el,
        file_data_name: file_name,
        multi_selection:multi_selection==1,
        headers: {
          token: jwt
        },
        filters: {
            mime_types: mine_types
        }
    });

    uploader.init();
    //文件被添加
    // uploader.bind('BeforeUpload', function (up, files) {
    //   if (typeof url === 'function'){
    //     up.settings.url = url()
    //   }else{
    //     up.settings.url = url
    //   }
    //   return true
    // });
    //文件被添加
    uploader.bind('FilesAdded', function (up, files) {
        if (file_add_cb) {
          file_add_cb(up, files);
        }
        if (auto_upload === "1" && url) {
          // console.log(up.settings)
            up.start();
        }
    });

    //上传进度
    uploader.bind('UploadProgress', function (up, file) {
        if (file_progrerss_cb) {
            window[file_progrerss_cb](up, file);
        }
    });


    //某个文件上传结算
    uploader.bind('FileUploaded', function (up, file, rst) {
      if (file_uploaded_cb) file_uploaded_cb(up, file, JSON.parse(rst.response));
    });

    //所有文件都已上传完成
    uploader.bind('UploadComplete', function (up, files) {
        if (file_complete_cb) {
          file_complete_cb(up, files);
        }
    });

    //出错
    uploader.bind('Error', function (up, err) {
      console.log(err)
        if (file_err_cb) {
            window[file_err_cb](up, err);
        }
    });
  };

  render(el)
}
