<?php $v4TableLanguage=['emptyTable'=>lang('V4.tableEmpty'),'loadingRecords'=>lang('V4.tableLoading'),'processing'=>lang('V4.tableLoading'),'search'=>lang('V4.tableSearch'),'lengthMenu'=>lang('V4.tableLength'),'info'=>lang('V4.tableInfo'),'infoEmpty'=>lang('V4.tableInfoEmpty'),'infoFiltered'=>lang('V4.tableInfoFiltered'),'zeroRecords'=>lang('V4.tableInfoEmpty'),'paginate'=>['next'=>lang('V4.tableNext'),'previous'=>lang('V4.tablePrevious'),'first'=>lang('V4.tableFirst'),'last'=>lang('V4.tableLast')]]; ?>
<script>
window.DentaTableLanguage=<?= json_encode($v4TableLanguage,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE) ?>;
if(window.jQuery?.fn.dataTable){jQuery.extend(true,jQuery.fn.dataTable.defaults,{language:window.DentaTableLanguage});}
</script>
