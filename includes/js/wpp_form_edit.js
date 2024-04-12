jQuery(document).ready(function($) {
    // 假设我们监听的是特定类型的字段，如文本字段
    $(document).on('input', '.field_setting input[id$="_placeholder"]', function() {
        var newPlaceholder = $(this).val();

        const arr = [
            'wppfield_cascader',
            'wppfield_idcard',
            'wppfield_phone',
        ]

        if (!arr.includes(GetSelectedField()['type'])) return;
        // 获取当前编辑的字段 ID
        var fieldId = GetSelectedField()["id"];

        // 更新实时预览
        $('#field_' + fieldId + ' input[type="text"]').attr('placeholder', newPlaceholder);
    });
});