import { Component, OnInit, ViewChild } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';
import { Observable } from 'rxjs/Observable';
import { throwError } from 'rxjs';
import { Http, Headers, RequestOptions } from '@angular/http';
import { environment } from '../../../../environments/environment';
import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

import { ModalComponent } from '../../partials/modal/modal.component';
import { IMyDpOptions, IMyDateModel } from 'mydatepicker';

@Component({
    selector: 'app-job-edit',
    templateUrl: './job-edit.component.html',
    styleUrls: ['./job-edit.component.scss']
})
export class JobEditComponent implements OnInit {
    job: any = {};
    jobImageLinkRoot: string = environment.UPLOAD_ENDPOINT + 'job/';
    image_default: string = 'assets/img/job_default.jpg';

    listImage: any = [];
    listVideo: any = [];

    listFiles: Array<any> = [];
    listOldFileId: string = "";
    listOldFileChange = [];
    order = 0;

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

    public myDatePickerOptions: IMyDpOptions = {
        dateFormat: 'yyyy/mm/dd',
    };

    public model: any = { date: null };
    public model2: any = { date: null };

    onDateChanged(event: IMyDateModel) {
        console.log(this.model);
    }


    constructor(private companyService: CompanyService, private restfulService: RestfulService, private router: Router, private route: ActivatedRoute, private http: Http) { }

    ngOnInit() {
        this.privilege = parseInt(this.companyService.getPrivilege());
        this.getJob();
        this.getListCategory();
        this.getListJobCategory();
        this.getListJobType();
        this.getListArea();
        this.getListStaff();
    }
    getJob() {
        let jobId = parseInt(this.route.snapshot.paramMap.get('jobId'));
        this.restfulService.doGet("company/job/detail/" + jobId, null).subscribe(res => {
            if (res.success) {
                this.job = res.data;
                this.listImage = this.job.images;
                this.listVideo = this.job.videos;
                this.getlistImage();
                this.getlistVideo();
                this.startDate = this.convertTimestamp(res.data.release_start_date);
                this.endDate = this.convertTimestamp(res.data.release_end_date);
                this.model = { date: this.convertTimestamp2(res.data.release_start_date) };
                this.model2 = { date: this.convertTimestamp2(res.data.release_end_date) };
            }
            else {
                this.modal.toast(res.error);
                this.router.navigate(['/company/job']);
            }
        })
    }

    async editJob(event: Event, form: any) {
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
                if (form[k]) {
                   formData.append(k, form[k]); 
                }
            }
        }
        formData.append('api_token', this.companyService.getToken());

        this.listFiles = this.listFiles.filter(Boolean);
        this.listOldFileChange = this.listOldFileChange.filter(Boolean);

        this.listOldFileChange.forEach(function(file, i) {
            formData.append('update_file_' + file.oldFile.id, file.oldFile);
        });

        formData.append('update_file_ids', JSON.stringify(this.listOldFileId));
        formData.append('new_file_length', this.listFiles.length.toString());

        let headers = new Headers();
        headers.append('Accept', 'application/json');
        let options = new RequestOptions({ headers: headers });
        const commonResponse = await this.http.post(
            environment.API_ENDPOINT + 'company/job/edit/' + this.job.id,
            formData,
            options)
                .map(res => res.json())
                .catch(error => throwError(error))
                .toPromise();
        this.handleResponse(commonResponse);
    }

    private async handleResponse(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            const self = this;
            if (this.listFiles.length || this.listOldFileChange.length) {
                //ファイルがあれば、以下の処理を行う
                //jobIdを取得
                const jobId = commonResponse.data;
                const staffId = self.companyService.getId();
                const baseUrl_getFileUploadUrl = `https://0kdfxnpw7c.execute-api.ap-northeast-1.amazonaws.com/${environment.ENVIRONMENT}/getFileUploadUrl`;
                let paramsHash: any = {
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
                        self.modal.toast("成功");
                        self.router.navigate(['/company/job']);
                    });
            } else {
                self.modal.toast("成功");
                self.router.navigate(['/company/job']);
            }
        } else {
            this.modal.toast(commonResponse.error);
        }
    }

    validateAgeInput() {
        if ((this.job.age_min && this.job.age_max && this.job.age_min > this.job.age_max) || (this.job.age_min && this.job.age_min < 1) || (this.job.age_max && this.job.age_max > 150)) {
            this.validateAge = false;
        }
        else this.validateAge = true;
    }

    
    private convertTimestamp(unix_timestamp) {
        var time = new Date(unix_timestamp * 1000);
        var year = time.getFullYear();
        var month = (time.getMonth() < 10) ? '0' + (time.getMonth() + 1) : (time.getMonth() + 1);
        var date = (time.getDate() < 10) ? '0' + time.getDate() : time.getDate();
        return year + '/' + month + '/' + date;
    }

    private convertTimestamp2(unix_timestamp) {
        let time = new Date(unix_timestamp * 1000);
        let year:number = time.getFullYear();
        let month:number = time.getMonth() + 1;
        let day:number = time.getDate();
        return { year: year, month: month, day: day }
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
            this.dateValidateModel.startDate = new Date(this.startDate);
            this.dateValidateModel.endDate = new Date(this.endDate);
        if (this.dateValidateModel.startDate && this.dateValidateModel.endDate) {
            if (this.dateValidateModel.startDate > this.dateValidateModel.endDate) {
                this.validateDate = false;
            }
            else {
                this.validateDate = true;
            }
        }
    }

    getListCategory() {
        this.restfulService.doGet('company/job/extend/category', null).subscribe(res => {
            if (res.success) {
                this.listCategory = res.data;
            }
            else {
                this.modal.toast(res.error);
            }
        });
    }

    getListJobCategory() {
        this.restfulService.doGet('company/job/extend/job-category', null).subscribe(res => {
            if (res.success) {
                this.listJobCategory = res.data;
            }
            else {
                this.modal.toast(res.error);
            }
        });
    }

    getListJobType() {
        this.restfulService.doGet('company/job/extend/job-type', null).subscribe(res => {
            if (res.success) {
                this.listJobType = res.data;
            }
            else {
                this.modal.toast(res.error);
            }
        });
    }

    getListArea() {
        this.restfulService.doGet('company/job/extend/area', null).subscribe(res => {
            if (res.success) {
                this.listArea = res.data;
            }
            else {
                this.modal.toast(res.error);
            }
        });
    }

    getListStaff() {
        this.restfulService.doGet('company/job/extend/staff', null).subscribe(res => {
            if (res.success) {
                this.listStaff = res.data;
            }
            else {
                this.modal.toast(res.error);
            }
        });
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
