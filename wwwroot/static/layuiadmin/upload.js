var droUploadUrl = '/system/upload/upload'; //上传地址
var droDelUrl = '/system/upload/delete';    //删除地址
function uploadImage(param) {
    var upload = layui.upload,$ = layui.$;

    //上传APP图片 单张
    var uploadInst = upload.render({
        elem: param.elem
        ,url: droUploadUrl
        ,accept:'images'
        ,multiple:false
        ,number:param.number
        ,acceptMime: 'image/*'
        ,before: function (obj) {
            var imgUrl = $(param.elementId).val();
            if (imgUrl != ''){
                if (imgUrl.indexOf("http") == -1){
                    //$.post(droDelUrl,{file_url:imgUrl});
                }
            }
        }
        ,done: function(res){
            //如果上传失败
            if(res.code != 1000){
                $(param.againUploadId).val(res.msg);
                return layer.msg('上传失败');
            }
            //上传完毕回调
            console.log(res);
            console.log("sss=",res);
            $(param.elementId).val(res.data.src);
            $(param.preview).attr('src',res.data.src); //图片链接（base64）
        }
        ,error: function(){
            //演示失败状态，并实现重传
            var demoText = $(param.againUploadId);
            demoText.html('<span style="color: #FF5722;">上传失败</span> <a class="layui-btn layui-btn-xs demo-reload">重试</a>');
            demoText.find('.demo-reload').on('click', function(){
                uploadInst.upload();
            });
        }
    });
}

//上传多张图片方法
function uploadImageMore(param) {
    var upload = layui.upload,$ = layui.$;
	var appImgArr = param.appImgArr = [];       //图片数组
    var appImgDelArr = param.appImgDelArr = []; //图片数组
	var elementId = $(param.elementId);         //存储的图片
    var elementDelId = $(param.elementDelId);   //存储已删除的图片
	
	//初始 Layui.upload控件
    var appImgUplod = upload.render({
        elem: param.elem
        ,url: droUploadUrl
        ,multiple: param.multiple
        ,number:param.number
        ,accept:'images'
        ,acceptMime: 'image/*'
        ,done: function(res){
			//上传完毕
			console.log(res);
			if(res.code == 1000){
				appImgArr.push(res.data.src);
				$(param.preview).append(
					'<div id="" class="file-iteme">' +
					'<div class="handle" src="'+res.data.src+'"><i class="layui-icon layui-icon-delete" style="font-size:8px;">删除</i></div>' +
					'<img style="width: 112px;height: 112px;" src='+ res.data.fullSrc +'></div>'
				)
				setImage(elementId,appImgArr);
			}else{
                return layer.msg(res.msg);
			}
        }
    });

    //多张图片初始化
    var imgStr = elementId.val();
    if (imgStr != ''){
        appImgArr = imgStr.split('||');
        $(appImgArr).each(function(index,item){
            var imgUrl = '';
            if (item.indexOf("http") != -1){imgUrl = item;}
            else {imgUrl = param.rootUrl + '/'+item;}
            $(param.preview).append(
                '<div id="" class="file-iteme">' +
                '<div class="handle" src="'+item+'"><i class="layui-icon layui-icon-delete" style="font-size:8px;">删除</i></div>' +
                '<img style="width: 112px;height: 112px;" src='+ imgUrl +'></div>'
            )
        });
    }

    //鼠标移动事件
    $(document).on("mouseenter mouseleave", ".file-iteme", function(event){
        if(event.type === "mouseenter"){
            //鼠标悬浮
            $(this).children(".info").fadeIn("fast");
            $(this).children(".handle").fadeIn("fast");
        }else if(event.type === "mouseleave") {
            //鼠标离开
            $(this).children(".info").hide();
            $(this).children(".handle").hide();
        }
    });

    // 删除图片
    $(document).on("click", ".file-iteme .handle", function(event){
        var imgSrc = $(this).attr('src');
        $(appImgArr).each(function (index,item) {
            if (item == imgSrc) {
                appImgArr.splice(index,1);
                appImgDelArr.push(item);
            }
        });
        $(this).parent().remove();
        setImage(elementId,appImgArr);
        setImage(elementDelId,appImgDelArr);
    });
}

//多张图片上传,为控件赋值
function setImage(ElementId,FileArr) {
    var file_str = '',$ = layui.$;
    if(FileArr.length){
        $(FileArr).each(function(index,item){
            if(item != undefined){
                if (file_str != ''){ file_str += '||'+item;}
                else { file_str = item; }
            }
        });
    }
    ElementId.val(file_str);
}


