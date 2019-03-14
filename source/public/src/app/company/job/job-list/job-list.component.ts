import { Component, OnInit, ViewChild } from '@angular/core';
import { DataShareService, MoveFrom } from '../../shared/services/data-share.service';
import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';
import { IMyDpOptions, IMyDateModel } from 'mydatepicker';
import { ModalComponent } from '../../partials/modal/modal.component';

@Component({
    selector: 'app-job-list',
    templateUrl: './job-list.component.html',
    styleUrls: ['./job-list.component.scss']
})
export class JobListComponent implements OnInit {

    constructor(private dataShareService: DataShareService, private restfulService: RestfulService, private companyService: CompanyService) { }

    ngOnInit() {
        this.movedFrom = this.dataShareService.getMovedFrom();
        this.getListJob();
        this.getListCategory();
    }
    ngOnDestroy() {
        this.dataShareService.setMovedFrom(MoveFrom.JobList);
    }

    private listJob: any;
    private listCategory: any;
    private keyword = "";
    private category_id = 0;
    private startDate = "";
    private endDate = "";

    private currentPage = 1;
    private perPage = 10;
    private paginates = [];
    private maxItem: number;
    private indexElement = (this.currentPage - 1) * this.perPage;

    private deleteId: number = 0;

    private movedFrom: MoveFrom;

    @ViewChild("modal") modal: ModalComponent;

    private myDatePickerOptions: IMyDpOptions = {
        dateFormat: 'yyyy/mm/dd',
    };

    onStartDateChanged(event: IMyDateModel) {
        this.startDate = event.formatted;
    }

    onEndDateChanged(event: IMyDateModel) {
        this.endDate = event.formatted;
    }

    getListJob() {
        let params = {
            'page_limit': this.perPage,
            'page_number': this.currentPage,
            'keyword': this.keyword,
            'category_id': this.category_id,
            'start_date': this.startDate,
            'end_date': this.endDate
        };
        this.restfulService.doGet("company/job/list", params).subscribe(res => {
            if (res.success) {
                this.listJob = res.data.data;
                this.maxItem = res.data.total;
                this.indexElement = (this.currentPage - 1) * this.perPage;
            }
            else {
                console.log(res.error);
            }
        });
    }

    pageChanged(event) {
        this.currentPage = event;
        this.getListJob();
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

    search(form) {
        this.keyword = form.keyword;
        this.category_id = form.category_id;
        this.currentPage = 1;
        this.getListJob();
    }

    toggleModalDelete(jobId) {
        this.modal.confirm(`求人を削除します。 <br>
            よろしければOKボタンを押してください`);
        this.deleteId = jobId;
    }

    confirmDelete(confirm: boolean) {
        if (confirm && this.deleteId) {
            this.restfulService.doPost('company/job/delete/' + this.deleteId, {}).subscribe(res => {
                if (res.success) {
                    if (this.listJob.length == 1 && this.currentPage > 1) {
                        this.currentPage = this.currentPage - 1;
                    }
                    this.getListJob();
                    this.deleteId = 0;
                    this.modal.toast('削除しました');
                }
                else {
                    console.log(res.error);
                }
            });
        }
    }
}
