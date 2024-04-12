/*
jQuery(document).ready(function ($) {
    $('#send_sms_verification').click(function () {
        // 禁用按钮
        $(this).prop('disabled', true);
        var phoneNumber = $('#input_23_146').val();
        if (!phoneNumber) {
            alert('请输入手机号码');
            return;
        }
        // 校验手机号码
        if (!/^1[3456789]\d{9}$/.test(phoneNumber)) {
            alert('请输入正确的手机号码');
            return;
        }
        $.ajax({
            url: "/wp-admin/admin-ajax.php",
            type: 'POST',
            data: {
                action: 'send_sms_verification',
                phone_number: phoneNumber
            },
            beforeSend: function () {
                // 发送前的操作
            },
            success: function (response) {
                if (response.data.code !== 0) {
                    alert(response.data.message);
                    return;
                }
                // 设置倒计时
                var count = 60;
                var timer = setInterval(function () {
                    count--;
                    $('#send_sms_verification').text(count + '秒后重新发送');
                    if (count <= 0) {
                        clearInterval(timer);
                        $('#send_sms_verification').text('发送验证码');
                        $('#send_sms_verification').prop('disabled', false);
                    }
                }, 1000);
            },
            error: function (error) {
                console.log(error);
                alert('发送失败，请稍后刷新重试');
            }
        });
    });
});
*/

jQuery(document).ready(function($) {

    $(".form-control").on("blur change", function () {
        if ($(this).is(":invalid")) {
            $(this).parent().addClass("was-validated")
            $(this).siblings(".invalid-feedback").show();
        } else {
            $(this).parent().removeClass("was-validated")
            $(this).siblings(".invalid-feedback").hide();
        }

    });
    /*封装表单验证*/
    $.fn.form_validation = function () {
        const a = $(this).closest('form').find(':invalid').length
        return a === 0;
    }
    $('#send_sms_verification').click(function(){
        const $this = $(this)


        if($('.input_sjh').is(':valid')){
            $('.input_sjh+.invalid-feedback').hide();
            $this.html('发送中…')
            $.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',
                data: {
                    action: 'send_sms_verification',
                    phone_number: $(".input_sjh").val(), // 将此处的手机号码替换为实际的手机号码

                },
                beforeSend: function(xhr) {
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    // 可以在此处添加其他自定义请求头
                },
                success: function(res) {
                    // 处理成功响应的逻辑
                    console.log(res);


                    if(res.success===true){
                        let count = 60;
                        const resend = setInterval(function () {
                            count--;
                            if (count > 0) {
                                $this.html("重新发送 " + count + "s").addClass("disabled");

                            } else {
                                clearInterval(resend);
                                $this.html("重新发送").toggleClass("disabled");
                            }
                        }, 1000);
                    }
                    else{
                        $this.html('重新发送')
                        $('.input_sjh+.invalid-feedback').html(res.data.message);                     $('.input_sjh').parent().addClass("was-validated")
                        $('.input_sjh+.invalid-feedback').show();
                    }



                },
                error: function(xhr, status, error) {
                    // 处理错误情况的逻辑
                    console.error(error);
                    alert('发送失败，请稍后刷新重试');
                }
            });
        }
        else{

            $('.input_sjh').parent().addClass("was-validated")
            $('.input_sjh').siblings(".invalid-feedback").show();

        }


    });





});