<script>
    var $eb = parent._mpApi,back = <?=$backUrl?>;
    $eb.notice('{$type}',{
        title:'{$msg}',
        desc:'{$info}' || null,
        duration:<?=$duration?>
    });
    !!back ? (location.replace(back)) : history.go(-1);
</script>