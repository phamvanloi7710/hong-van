<?php

return [
    'created' => '页面已创建。',
    'updated' => '页面元数据已更新。',
    'draft_saved' => 'Page Builder 草稿已保存。',
    'slug_taken' => '该语言的 slug 已被使用。',
    'validation' => [
        'payload_too_large' => '文档超过允许的大小。', 'schema_version' => '不支持此 PageDocument 版本。',
        'ulid' => '该值必须是有效的 ULID。', 'blocks_list' => 'Blocks 必须是顺序数组。', 'block_object' => 'Block 必须是对象。',
        'block_id' => 'Block ID 无效。', 'duplicate_id' => 'Block ID 重复。', 'unknown_block' => 'Block 类型未注册。',
        'block_version' => '不支持此 Block 版本。', 'invalid_parent' => '该 Block 不允许位于根级。', 'invalid_child' => '父 Block 不允许此子 Block。',
        'children_list' => 'Children 必须是顺序数组。', 'children_not_allowed' => '该 Block 不允许子 Block。', 'children_min' => '子 Block 数量少于允许值。',
        'children_max' => '子 Block 数量超过允许值。', 'block_depth' => 'Block 超过该布局允许的嵌套深度。', 'too_many_blocks' => '文档中的 Block 过多。',
        'too_deep' => 'Block 树超过允许深度。', 'object' => '该值必须是对象。', 'required' => '此字段为必填项。',
        'unknown_field' => '此字段不在允许列表中。', 'type' => '数据类型无效。', 'enum' => '该值不在允许列表中。', 'max_length' => '字符串超过允许长度。', 'pattern' => '值的格式无效。',
        'mobile_grid_columns' => '移动端网格只允许一列或两列。', 'background_media_required' => '媒体背景需要有效的媒体 public ID。',
        'columns_children' => '该分栏预设必须有 :count 个子 Block。',
        'executable_payload' => '不允许 Blade、PHP、JavaScript 或事件处理器。', 'unknown_reference' => 'Binding 引用了不存在的 Block。',
        'cycle' => 'Binding 在 Block 之间形成循环引用。', 'migration_missing' => '该 Block 版本缺少连续迁移。',
    ],
];
