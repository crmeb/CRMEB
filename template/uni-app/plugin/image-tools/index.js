function getLocalFilePath(path) {
    if (path.indexOf('_www') === 0 || path.indexOf('_doc') === 0 || path.indexOf('_documents') === 0 || path.indexOf('_downloads') === 0) {
        return path
    }
    if (path.indexOf('file://') === 0) {
        return path
    }
    if (path.indexOf('/storage/emulated/0/') === 0) {
        return path
    }
    if (path.indexOf('/') === 0) {
        var localFilePath = plus.io.convertAbsoluteFileSystem(path)
        if (localFilePath !== path) {
            return localFilePath
        } else {
            path = path.substr(1)
        }
    }
    return '_www/' + path
}

export function pathToBase64(path) {
    return new Promise(function(resolve, reject) {
        if (typeof window === 'object' && 'document' in window) {
            if (typeof FileReader === 'function') {
                var xhr = new XMLHttpRequest()
                xhr.open('GET', path, true)
                xhr.responseType = 'blob'
                xhr.onload = function() {
                    if (this.status === 200) {
                        let fileReader = new FileReader()
                        fileReader.onload = function(e) {
                            resolve(e.target.result)
                        }
                        fileReader.onerror = reject
                        fileReader.readAsDataURL(this.response)
                    }
                }
                xhr.onerror = reject
                xhr.send()
                return
            }
            var canvas = document.createElement('canvas')
            var c2x = canvas.getContext('2d')
            var img = new Image
            img.onload = function() {
                canvas.width = img.width
                canvas.height = img.height
                c2x.drawImage(img, 0, 0)
                resolve(canvas.toDataURL())
                canvas.height = canvas.width = 0
            }
            img.onerror = reject
            img.src = path
            return
        }
        if (typeof plus === 'object') {
            plus.io.resolveLocalFileSystemURL(getLocalFilePath(path), function(entry) {
                entry.file(function(file) {
                    var fileReader = new plus.io.FileReader()
                    fileReader.onload = function(data) {
                        resolve(data.target.result)
                    }
                    fileReader.onerror = function(error) {
                        reject(error)
                    }
                    fileReader.readAsDataURL(file)
                }, function(error) {
                    reject(error)
                })
            }, function(error) {
                reject(error)
            })
            return
        }
        if (typeof wx === 'object' && wx.canIUse('getFileSystemManager')) {
            wx.getFileSystemManager().readFile({
                filePath: path,
                encoding: 'base64',
                success: function(res) {
                    resolve('data:image/png;base64,' + res.data)
                },
                fail: function(error) {
                    reject(error)
                }
            })
            return
        }
        reject(new Error('not support'))
    })
}

export function base64ToPath(base64) {
    return new Promise(function(resolve, reject) {
        if (typeof window === 'object' && 'document' in window) {
            base64 = base64.split(',')
            var type = base64[0].match(/:(.*?);/)[1]
            var str = atob(base64[1])
            var n = str.length
            var array = new Uint8Array(n)
            while (n--) {
                array[n] = str.charCodeAt(n)
            }
            return resolve((window.URL || window.webkitURL).createObjectURL(new Blob([array], { type: type })))
        }
        var extName = base64.match(/data\:\S+\/(\S+);/)
        if (extName) {
            extName = extName[1]
        } else {
            reject(new Error('base64 error'))
        }
        var fileName = Date.now() + '.' + extName
        if (typeof plus === 'object') {
            var bitmap = new plus.nativeObj.Bitmap('bitmap' + Date.now())
            bitmap.loadBase64Data(base64, function() {
                var filePath = '_doc/uniapp_temp/' + fileName
                bitmap.save(filePath, {}, function() {
                    bitmap.clear()
                    resolve(filePath)
                }, function(error) {
                    bitmap.clear()
                    reject(error)
                })
            }, function(error) {
                bitmap.clear()
                reject(error)
            })
            return
        }
        if (typeof wx === 'object' && wx.canIUse('getFileSystemManager')) {
            var filePath = wx.env.USER_DATA_PATH + '/' + fileName
            wx.getFileSystemManager().writeFile({
                filePath: filePath,
                data: base64.replace(/^data:\S+\/\S+;base64,/, ''),
                encoding: 'base64',
                success: function() {
                    resolve(filePath)
                },
                fail: function(error) {
                    reject(error)
                }
            })
            return
        }
        reject(new Error('not support'))
    })
}