import { Component, OnInit, ViewChild } from '@angular/core';
import { Router } from '@angular/router';
import { FormBuilder, FormControl, FormGroup, Validators } from "@angular/forms";

import { Http, Headers, RequestOptions } from '@angular/http';
import { environment } from '../../../../environments/environment';

import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

import { ModalComponent } from '../../partials/modal/modal.component';
import { IMyDpOptions, IMyDateModel } from 'mydatepicker';

import { throwError } from 'rxjs'

@Component({
	selector: 'app-job-add',
	templateUrl: './job-add.component.html',
	styleUrls: ['./job-add.component.scss']
})
export class JobAddComponent implements OnInit {
	image_default: string = 'assets/img/job_default.jpg';
	totalFile: number = 0;
	listFiles: Array<any> = [];

	minAge;
	maxAge;
	startDate;
	endDate;
	validateAge = true;
	validateDate = true;

	private listCategory: any;
	private listJobCategory: any;
	private listJobType: any;
	private listArea: any;
	private listStaff: any;
	private privilege: number;

	@ViewChild("modal") modal: ModalComponent;

	private myDatePickerOptions: IMyDpOptions = {
		dateFormat: 'yyyy/mm/dd',
	};

	constructor(private companyService: CompanyService, private restfulService: RestfulService, private router: Router, private http: Http) { }

	ngOnInit() {
		this.privilege = parseInt(this.companyService.getPrivilege());
		this.getListCategory();
		this.getListJobCategory();
		this.getListJobType();
		this.getListArea();
		this.getListStaff();
		this.moreImage();
		this.moreVideo();
	}

	async addJob(event: Event, form: any) {
		form.staff_id = this.companyService.getId();
		form.company_id = this.companyService.getCompanyId();
		if (this.privilege == 2) {
			form.management_staff = this.companyService.getId();
		}
		form.release_start_date = this.startDate;
		form.release_end_date = this.endDate;
		let formData: FormData = new FormData();
		for (var k in form) {
			if (form[k]) {
				formData.append(k, form[k]);
			}
		}
		this.listFiles = this.listFiles.filter(Boolean);
		formData.append('api_token', this.companyService.getToken());
		formData.append('staff_id', this.companyService.getId());
		let headers = new Headers();
		headers.append('Accept', 'application/json');
		let options = new RequestOptions({ headers: headers });
		let commonResponse = await this.http.post(environment.API_ENDPOINT + 'company/job/add', formData, options)
			.map(res => res.json())
			.catch(error => throwError(error))
            .toPromise();
	    this.handleResponse(commonResponse);
	}

	async handleResponse(commonResponse: any) {
		if (commonResponse == null) return;
		if (commonResponse.success) {
            const self = this;
            if (this.listFiles.length) {
                //ファイルがあれば、以下の処理を行う
                //jobIdを取得
                const jobId = commonResponse.data;
                const staffId = self.companyService.getId();
                const baseUrl_getFileUploadUrl = `https://0kdfxnpw7c.execute-api.ap-northeast-1.amazonaws.com/${environment.ENVIRONMENT}/getFileUploadUrl`;
                let paramsHash = {
                    user_type: 'staff',
                    user_id: staffId,
                    job_id: jobId,
                    file_name: '',
                };
                const headersGetUrl = new Headers({
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${self.companyService.getToken()}`,
                });
                const _uploadFunction = async (file, binary) => {
                    //APIGatewayEndpointを叩く
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
                Promise.all(readPromises)
                    .then(async (results) => {
                        for (let i=0; i < results.length; i++) {
                            console.log(`fileUploading ${i}`);
                            await _uploadFunction(self.listFiles[i], results[i]);
                        }
			            self.modal.toast("求人の追加に成功しました。");
			            self.router.navigate(['/company/job']);
                    });
            } else {
			    self.modal.toast("求人の追加に成功しました。");
			    self.router.navigate(['/company/job']);
            }
		} else {
			this.modal.toast(commonResponse.error);
		}
	}


	getListCategory() {
		this.restfulService.doGet('company/job/extend/category', null).subscribe(res => {
			if (res.success) {
				this.listCategory = res.data;
			}
			else {
				console.log(res.error);
			}
		});
	}

	getListJobCategory() {
		this.restfulService.doGet('company/job/extend/job-category', null).subscribe(res => {
			if (res.success) {
				this.listJobCategory = res.data;
			}
			else {
				console.log(res.error);
			}
		});
	}

	getListJobType() {
		this.restfulService.doGet('company/job/extend/job-type', null).subscribe(res => {
			if (res.success) {
				this.listJobType = res.data;
			}
			else {
				console.log(res.error);
			}
		});
	}

	getListArea() {
		this.restfulService.doGet('company/job/extend/area', null).subscribe(res => {
			if (res.success) {
				this.listArea = res.data;
			}
			else {
				console.log(res.error);
			}
		});
	}

	getListStaff() {
		this.restfulService.doGet('company/job/extend/staff', null).subscribe(res => {
			if (res.success) {
				this.listStaff = res.data;
			}
			else {
				console.log(res.error);
			}
		});
	}

	validateAgeInput() {
		if ((this.minAge && this.maxAge && this.minAge > this.maxAge) || (this.minAge < 1 || this.maxAge > 150)) {
			this.validateAge = false;
		}
		else this.validateAge = true;
	}

	dateValidateModel = { startDate: null, endDate: null };
	onStartDateChanged(event: IMyDateModel) {
		this.startDate = event.formatted;
		this.validateDateInput(event.formatted, 1);
	}

	onEndDateChanged(event: IMyDateModel) {
		this.endDate = event.formatted;
		this.validateDateInput(event.formatted, 2);
	}

	validateDateInput(date, type) {
		if (type == 1) {
			this.dateValidateModel.startDate = new Date(date);
		} else {
			this.dateValidateModel.endDate = new Date(date);
		}
		if (this.dateValidateModel.startDate && this.dateValidateModel.endDate) {
			if (this.dateValidateModel.startDate > this.dateValidateModel.endDate) {
				this.validateDate = false;
			}
			else {
				this.validateDate = true;
			}
		}
	}

	moreImage() {
		this.totalFile++;
		this.listFiles[this.totalFile] = null;
		let html = `<div class="col-md-3 mb-3" id="preview-${this.totalFile}">
						<label for="input-image-${this.totalFile}" id="label-image${this.totalFile}" class="btn btn-sm btn-success">画像を選択</label> <label class="btn btn-sm btn-danger" id="remove-preview-${this.totalFile}"><small>削除する</small></label><br>
						<input type="file" accept="image/*" class="form-control-file d-none" id="input-image-${this.totalFile}" name="image_{this.totalFile}">
						<div class="preview-div">
							<img src="${this.image_default}" id="image-${this.totalFile}" class="job-image preview-image">
						</div>
					</div>`;
		document.getElementById("image-group").insertAdjacentHTML('beforeend', html);
		document.getElementById('input-image-' + this.totalFile).addEventListener('change', (event) => {
			this.onFileChanged(event, 1);
		});
		document.getElementById('remove-preview-' + this.totalFile).addEventListener('click', (event) => {
			this.removePreview(event);
		});
	}

	moreVideo() {
		this.totalFile++;
		this.listFiles[this.totalFile] = null;
		let html = `<div class="col-md-3 mb-3" id="preview-${this.totalFile}">
						<label for="input-video-${this.totalFile}" id="label-video${this.totalFile}" class="btn btn-sm btn-success">画像を選択</label> <label class="btn btn-sm btn-danger" id="remove-preview-${this.totalFile}"><small>削除する</small></label><br>
						<input type="file" accept="video/*" class="form-control-file d-none" id="input-video-${this.totalFile}" name="video_{this.totalFile}">
						<div class="preview-div">
							<video width="300" height="300" controls id="video-${this.totalFile}"></video>
						</div>
					</div>`;
		document.getElementById("video-group").insertAdjacentHTML('beforeend', html);
		document.getElementById('input-video-' + this.totalFile).addEventListener('change', (event) => {
			this.onFileChanged(event, 2);
		});
		document.getElementById('remove-preview-' + this.totalFile).addEventListener('click', (event) => {
			this.removePreview(event);
		});
	}

	onFileChanged(event, type) {
		let file = event.target.files[0];
		if (file) {
			let fileName = file.name;
			let fileExtension = fileName.substring(fileName.lastIndexOf('.') + 1).toLowerCase();
			let imageExtension = ['png', 'jpeg', 'jpg', 'gif', 'bmp'];
			let videoExtension = ['mp4', 'webm', 'ogg', 'wmv', 'avi', 'flv', 'm4v'];
			if (type == 1 && !imageExtension.includes(fileExtension)) {
				this.modal.toast("写真はPNG、JPG、JPEG、GIF、BMPのファイルタイプのみを許可します。");
			}
			else if (type == 2 && !videoExtension.includes(fileExtension)) {
				this.modal.toast("ビデオはMP4、WEBM、OGG、WMV、AVI、FLV、M4Vファイルタイプのみを許可します。");
			}
			else {
				let currentOrder = parseInt(event.currentTarget.id.replace(/[^0-9]/g, ''), 10);
				let obj = {};
				obj['type'] = (type == 1) ? 'image' : 'video';
				obj['file'] = file;

				let myReader: FileReader = new FileReader();
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

	removePreview(event) {
		let currentOrder = parseInt(event.currentTarget.id.replace(/[^0-9]/g, ''), 10);
		let elem = document.getElementById("preview-" + currentOrder);
		elem.parentElement.removeChild(elem);
		this.listFiles[currentOrder] = null;
	}
}
