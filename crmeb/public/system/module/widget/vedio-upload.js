function UserFactory(name, file, client, res, callback) {
    function OSS() {
        var suffix = file.name.substr(file.name.indexOf("."));
        var obj = uploadName();
        var storeAs = obj + suffix;
        client.multipartUpload(storeAs, file).then(function (result) {
            var url = result.res.requestUrls[0];
            var length = url.lastIndexOf('?');
            var imgUrl = url.substr(0, length);
            callback(imgUrl, file, 3);
        }).catch(function (err) {
            console.log(err);
        });
    }

    function COS() {
        var selectedFile = file;
        filename = selectedFile.name;
        filename = uploadName(filename);
        client.putObject({
            Bucket: res.bucket,
            Region: res.region,
            Key: filename,
            StorageClass: 'STANDARD',
            Body: selectedFile,
        }, function (err, data) {
            callback("http://" + data.Location, file, 4);
        });
    }

    function QINIU() {
        var files = file;
        var key = uploadName();
        var token = client; //从服务器拿的并存在本地data里
        var putExtra = {
            fname: '',
            params: {},
            mimeType: null,
        };
        var config = {
            useCdnDomain: true, //使用cdn加速
        };
        var observable = qiniu.upload(files, key, token, putExtra, config);
        observable.subscribe({
            next: (result) => {
                // 主要用来展示进度
                console.warn(result);
            },
            error: (err) => {
                console.log(err);
            },
            complete: (re) => {
                callback("http://" + res.region + "/" + re.key, file, 2);
            },
        });
    }

    function uploadName() {
        const data =
            ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r",
                "s", "t", "u", "v", "w", "x", "y", "z"];
        let nums = "";
        for (let i = 0; i < 32; i++) {
            const r = parseInt(Math.random() * 35);
            nums += data[r];
        }
        return nums;
    }

    switch (name) {
        case 'OSS':
            return new OSS();
            break;
        case 'COS':
            return new COS();
            break;
        case 'QINIU':
            return new QINIU();
            break;
    }
}