import { Component, OnInit, ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { Observable } from 'rxjs/Observable';
import { throwError } from 'rxjs';
import { FormBuilder, FormGroup, Validators } from "@angular/forms";
import { Http, Headers, RequestOptions } from '@angular/http';
import { environment } from '../../../../environments/environment';

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

import { ModalComponent } from '../../partials/modal/modal.component';

@Component({
	selector: 'app-company-edit',
	templateUrl: './company-edit.component.html',
	styleUrls: ['./company-edit.component.scss']
})

export class CompanyEditComponent implements OnInit {

    company: any = {};
    companyImageLinkRoot: string = environment.UPLOAD_ENDPOINT + 'company/';
    image_default: string = 'assets/img/job_default.jpg';
    
    listImage: any = [];
    listVideo: any = [];

    listFiles: Array<any> = [];
    listOldFileId: string = "";
    listOldFileChange = [];
    order = 0;

    listBusinessField: any;

    @ViewChild("modal") modal: ModalComponent;
    
	constructor(private companyService: CompanyService, private restfulService: RestfulService, private router: Router, private http: Http) { }

	ngOnInit() {
		this.getCompany();
        this.getBusinessField();
	}

	getCompany() {
		let companyId = this.companyService.getCompanyId();
		this.restfulService.doGet("company/detail/" + companyId, null).subscribe(res => {
			if (res.success) {
				this.company = res.data;
                this.listImage = this.company.images;
                this.listVideo = this.company.videos;
                this.getlistImage();
                this.getlistVideo();

			}
			else {
				alert(res.error);
			}
		});
	}

    getBusinessField() {
        this.restfulService.doGet("company/business-field", null).subscribe(res => {
            if (res.success) {
                this.listBusinessField = res.data;
            }
            else {
                alert(res.error);
            }
        });
    }

	async editCompany(form) {
		let formData: FormData = new FormData();
		for (var k in this.company) {
			if(this.company[k]){
				formData.append(k, this.company[k]);
			}
		}
		formData.append('api_token', this.companyService.getToken());

        this.listFiles = this.listFiles.filter(Boolean);
        this.listOldFileChange = this.listOldFileChange.filter(Boolean);


        this.listOldFileChange.forEach(function(file, i) {
            formData.append('update_file_' + file.oldId, file.file);
        });

        formData.append('new_file_length', this.listFiles.length.toString());
        formData.append('update_file_ids', JSON.stringify(this.listOldFileId));

		let headers = new Headers();
		headers.append('Accept', 'application/json');
		let options = new RequestOptions({ headers: headers });
		const commonResponse = await this.http.post(environment.API_ENDPOINT + 'company/edit/' + this.company.id, formData, options)
			.map(res => res.json())
            .catch(error => throwError(error))
            .toPromise();
        this.handleResponse(commonResponse);
	}

	private async handleResponse(commonResponse: any) {
		if (commonResponse == null) return;
		if (commonResponse.success) {
            if (this.listFiles.length || this.listOldFileChange.length) {
                const self = this;
                //ファイルがあれば、以下の処理を行う
                //jobIdを取得
                const jobId = commonResponse.data;
                const staffId = self.companyService.getId();
                const baseUrl_getFileUploadUrl = `https://0kdfxnpw7c.execute-api.ap-northeast-1.amazonaws.com/${environment.ENVIRONMENT}/getFileUploadUrl`;
                let paramsHash: any = {
                    user_type: 'staff',
                    user_id: staffId,
                    company_id: this.companyService.getCompanyId(),
                    file_name: '',
                };
                const headersGetUrl = new Headers({
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${self.companyService.getToken()}`,
                });

                const _uploadFunction = async (file, binary) => {
                    //APIGatewayEndpointを叩く
                    if (file.oldFile) {
                        const path = file.oldFile.path;
                        const splittedPath = path.split('/');
                        const key = splittedPath.slice(4).join('/');
                        paramsHash.update_file_key = key;
                    }
                    paramsHash.file_name = file.file.name;
                    let options = new RequestOptions({ headers: headersGetUrl, params: paramsHash });
                    const res_url = await self.http.get(baseUrl_getFileUploadUrl, options)
                        .map(self.restfulService.extractData)
                        .catch(self.restfulService.handleError)
                        .toPromise();
                    if (res_url.success) {
                        //uploadUrl取得
                        const uploadUrl = res_url.data;
                        //アップロード
                        let res_put = await self.http.put(uploadUrl, binary)
                            .catch(error => throwError(error))
                            .toPromise();
                        console.log(res_put);
                    }
                };
                const _readAsArrayBuffer = (file) => {
                    const tmpFileReader = new FileReader();
                    return new Promise((resolve, reject) => {
                        tmpFileReader.onerror = () => {
                            tmpFileReader.abort();
                            reject(new DOMException("Problem parsing input file."));
                        };

                        tmpFileReader.onload = () => {
                            resolve(tmpFileReader.result);
                        };
                        tmpFileReader.readAsArrayBuffer(file);
                    });
                };
                let readPromises = [];
                for (let i=0; i < self.listFiles.length; i++) {
                    readPromises.push(_readAsArrayBuffer(self.listFiles[i].file));
                }
                for (let i=0; i < self.listOldFileChange.length; i++) {
                    readPromises.push(_readAsArrayBuffer(self.listOldFileChange[i].file));
                }
                Promise.all(readPromises)
                    .then(async (results) => {
                        for (let i=0; i < results.length; i++) {
                            console.log(`fileUploading ${i}`);
                            if (i < self.listFiles.length) {
                                await _uploadFunction(self.listFiles[i], results[i]);
                            } else {
                                await _uploadFunction(self.listOldFileChange[i - self.listFiles.length], results[i]);
                            }
                        }
                        this.modal.toast("編集完了");
			            this.router.navigate(['/company/info']);
                    });
            } else {
                this.modal.toast("編集完了");
			    this.router.navigate(['/company/info']);
            }
		} else {
			alert(commonResponse.error);
		}
	}

    getlistVideo() {
        let that = this;
        this.listVideo.forEach(function(video) {
            that.order++;
            let html = `<div class="col-md-3 mb-3" id="preview-${that.order}">
                            <label for="input-${that.order}" id="label-${that.order}" class="btn btn-sm btn-success">画像を選択</label> <label class="btn btn-sm btn-danger" id="remove-preview-${that.order}""><small>削除する</small></label><br>
                            <input type="file" accept="video/*" class="form-control-file d-none" id="input-${that.order}" name="video_${that.order}">
                            <div class="preview-div">
                                <video src="${video.path}" id="file-${that.order}" controls width="300" height="300">
                            </div>
                        </div>`;
            document.getElementById("video-group").insertAdjacentHTML('beforeend', html);
            document.getElementById('input-' + that.order).addEventListener('change', (event) => {
                that.onOldFileChanged(event, video, 2);
            });
            document.getElementById('remove-preview-' + that.order).addEventListener('click', (event) => {
                that.removePreview(event, video);
            });
        });
    }

    getlistImage() {
        let that = this;
        this.listImage.forEach(function(image, i) {
            that.order++;
            let html = `<div class="col-md-3 mb-3" id="preview-${that.order}">
                            <label for="input-${that.order}" id="label-${that.order}" class="btn btn-sm btn-success">画像を選択</label> <label class="btn btn-sm btn-danger" id="remove-preview-${that.order}"><small>削除する</small></label><br>
                            <input type="file" accept="image/*" class="form-control-file d-none" id="input-${that.order}" name="image_${that.order}">
                            <div class="preview-div">
                                <img src="${image.path}" id="file-${that.order}" class="job-image preview-image">
                            </div>
                        </div>`;
            document.getElementById("image-group").insertAdjacentHTML('beforeend', html);
            document.getElementById('input-' + that.order).addEventListener('change', (event) => {
                that.onOldFileChanged(event, image, 1);
            });
            document.getElementById('remove-preview-' + that.order).addEventListener('click', (event) => {
                that.removePreview(event, image);
            });
        });
    }

    onOldFileChanged(event, oldFile, type) {
        let file = event.target.files[0];
        let myReader: FileReader = new FileReader();
        let currentOrder = parseInt(event.currentTarget.id.replace(/[^0-9]/g, ''), 10);
        if (file) {
            let validate = this.validateFile(file.name, type);
            if (validate) {
                if (oldFile) {
                    let a = { 'oldFile': oldFile, 'file': file };
                    this.listOldFileId += ',' + oldFile.id;
                    this.listOldFileChange[currentOrder] = a;
                }
                myReader.onload = (e: any) => {
                    document.getElementById('file-' + currentOrder).setAttribute("src", e.target.result);
                }
                myReader.readAsDataURL(event.target.files[0]);
            }
        }
    }

    validateFile(fileName, type) {
        let fileExtension = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
        let imageExtension = ['png', 'jpeg', 'jpg', 'gif', 'bmp'];
        let videoExtension = ['mp4', 'webm', 'ogg', 'wmv', 'avi', 'flv', 'm4v'];
        if (type == 1 && !imageExtension.includes(fileExtension)) {
            this.modal.toast("写真はPNG、JPG、JPEG、GIF、BMPのファイルタイプのみを許可します。");
            return false;
        }
        else if (type == 2 && !videoExtension.includes(fileExtension)) {
            this.modal.toast("ビデオはMP4、WEBM、OGG、WMV、AVI、FLV、M4Vファイルタイプのみを許可します。");
            return false;
        }
        return true;
    }

    onFileChanged(event, type) {
        let file = event.target.files[0];
        let myReader: FileReader = new FileReader();
        let currentOrder = parseInt(event.currentTarget.id.replace(/[^0-9]/g, ''), 10);
        if (file) {
            let validate = this.validateFile(file.name, type);
            if (validate) {
                let obj = {};
                obj['type'] = (type == 1) ? 'image' : 'video';
                obj['file'] = file;
                this.listFiles[currentOrder] = obj;
                myReader.onload = (e: any) => {
                    if (type == 1) {
                        document.getElementById('image-' + currentOrder).setAttribute("src", e.target.result);
                    }
                    if (type == 2) {
                        document.getElementById('video-' + currentOrder).setAttribute("src", e.target.result);
                    }
                }
                myReader.readAsDataURL(event.target.files[0]);
            }
        }
    }

    removePreview(event, file = null) {
        let currentOrder = parseInt(event.currentTarget.id.replace(/[^0-9]/g, ''), 10);
        if (file) {
            this.listOldFileId += ',' + file.id;
            this.listOldFileChange[currentOrder] = null;
        }
        else {
            this.listFiles[currentOrder] = null;
        }
        var elem = document.getElementById("preview-" + currentOrder);
        elem.parentElement.removeChild(elem);
    }

    moreImage() {
        this.order++;
        this.listFiles[this.order] = null;
        let html = `<div class="col-md-3 mb-3" id="preview-${this.order}">
                        <label for="input-image-${this.order}" id="label-image${this.order}" class="btn btn-sm btn-success">画像を選択</label> <label class="btn btn-sm btn-danger" id="remove-preview-${this.order}"><small>削除する</small></label><br>
                        <input type="file" accept="image/*" class="form-control-file d-none" id="input-image-${this.order}" name="image_${this.order}">
                        <div class="preview-div">
                            <img src="${this.image_default}" id="image-${this.order}" class="job-image preview-image">
                        </div>
                    </div>`;
        document.getElementById("image-group").insertAdjacentHTML('beforeend', html);
        document.getElementById('input-image-' + this.order).addEventListener('change', (event) => {
            this.onFileChanged(event, 1);
        });
        document.getElementById('remove-preview-' + this.order).addEventListener('click', (event) => {
            this.removePreview(event);
        });
    }

    moreVideo() {
        this.order++;
        this.listFiles[this.order] = null;
        let html = `<div class="col-md-3 mb-3" id="preview-${this.order}">
                        <label for="input-video-${this.order}" id="label-video${this.order}" class="btn btn-sm btn-success">画像を選択</label> <label class="btn btn-sm btn-danger" id="remove-preview-${this.order}"><small>削除する</small></label><br>
                        <input type="file" accept="video/*" class="form-control-file d-none" id="input-video-${this.order}" name="video_${this.order}">
                        <div class="preview-div">
                            <video width="300" height="300" controls id="video-${this.order}"></video>
                        </div>
                    </div>`;
        document.getElementById("video-group").insertAdjacentHTML('beforeend', html);
        document.getElementById('input-video-' + this.order).addEventListener('change', (event) => {
            this.onFileChanged(event, 2);
        });
        document.getElementById('remove-preview-' + this.order).addEventListener('click', (event) => {
            this.removePreview(event);
        });
    }
}
