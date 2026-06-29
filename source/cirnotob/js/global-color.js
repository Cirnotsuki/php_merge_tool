"use strict";
const $ = jQuery;
let timeoutId = null;

const getMaxID = function () {
  let value = 1;
  $(".color-item").each(function () {
    const id = parseInt($(this).data("id"));
    if (id > value) {
      value = id;
    }
  });
  return value;
};

const refreshColorPickers = function () {
  $(".color-picker").each(function (index, picker) {
    // console.log(picker);

    const $picker = jQuery(picker);
    if ($picker.hasClass("wp-color-picker")) return; // 避免重复初始化

    // 修复：使用jQuery方式初始化（WP颜色选择器标准用法）
    $picker.wpColorPicker({
      // change: function(event, ui) {
      //     // 同步预览色块颜色
      //     const colorItem = this.closest('.color-item');
      //     if (colorItem) {
      //         colorItem.querySelector('.color-preview').style.background = ui.color.toString();
      //     }
      // }
    });
  });
};

const createColorPickerItem = function (_color) {
  const color = {
    id: getMaxID(),
    code: "#cccccc",
    name: "",
    classes: "",
    ..._color,
  };

  return $("#color-template")
    .html()
    .replace(/\{ID\}/g, color.id)
    .replace(/\{CODE\}/g, color.code)
    .replace(/\{NAME\}/g, color.name)
    .replace(/\{CLASSES\}/g, color.classes);
};

// 初始化WordPress原生拾色器（修复版）
const initColorPickers = () => {
  clearTimeout(timeoutId);
  // 修复：等待jQuery和wp.colorPicker加载完成
  if (
    typeof jQuery === "undefined" ||
    typeof jQuery.wp === "undefined" ||
    !jQuery.wp.wpColorPicker
  ) {
    // 延迟重试，确保脚本加载完成
    timeoutId = setTimeout(initColorPickers, 100);
    return;
  }

  const existingTemplate = $("#color-template").html();
  const colorList = $("#color-list");

  // PHP 传递颜色数据到 JS（安全转义）
  const existingColors = JSON.parse(
    decodeURIComponent($(".global-color-wrap").data("colors")) || "[]",
  );

  colorList.html(
    '<p class="text-muted">暂无颜色，点击“添加新颜色”按钮开始添加。</p>',
  );

  $(".text-muted").hide();
  if (existingColors.length === 0) {
    $(".text-muted").show();
  } else {
    colorList.append(
      existingColors.map((color) => createColorPickerItem(color)).join(""),
    );
  }

  refreshColorPickers();
};

$(document).ready(function () {
  // 初始化拾色器
  initColorPickers();
});

// 添加新颜色
$("#add-new-color").on("click", function () {
  // 如果是空列表，先清空提示文字
  $(".text-muted").hide();
  console.log(createColorPickerItem());

  $("#color-list").append(createColorPickerItem());
  // 重新初始化拾色器
  refreshColorPickers();
});

// 删除颜色
$(".cirnotob-global-color").on("click", function (e) {
  const $target = $(e.target);

  if ($target.hasClass("remove-color")) {
    if (confirm("确定要删除这个颜色吗？")) {
      $target.closest(".color-item").remove();
    }
  }
});

// 表单提交前整理数据
$("#color-manager-form").submit(function () {
  // 收集所有颜色数据并更新到隐藏字段
  const colors = [];
  $(".color-item").each(function (index, item) {
    const $item = $(item);
    const id = $item.data("id");

    const $nameInput = $item.find(`input[name="color_name[${id}]"]`);
    const $classesInput = $item.find(`input[name="color_classes[${id}]"]`);
    const $codeInput = $item.find(`input[name="color_code[${id}]"]`);

    if ($nameInput && $classesInput && $codeInput) {
      colors.push({
        id: +id,
        name: $.trim($nameInput.val() || ""),
        classes: $.trim($classesInput.val() || ""),
        code: $.trim($codeInput.val() || ""),
      });
    }
  });

  // 更新隐藏的设置字段
  const colorName = $(".global-color-wrap").data("color-name");
  const settingsField = $('input[name="' + colorName + '"]');

  if (settingsField.length > 0) {
    settingsField.val(JSON.stringify(colors));
  }
});
