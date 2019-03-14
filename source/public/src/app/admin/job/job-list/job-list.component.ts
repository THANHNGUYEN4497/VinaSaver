import { Component, OnInit, ViewChild } from '@angular/core';
import { RestfulService } from '../../../shared/services/restful.service';
import { AdminService } from '../../shared/services/admin.service';
import { Router } from '@angular/router';
import { IMyDpOptions, IMyDateModel } from 'mydatepicker';
import { ModalComponent } from "../../partials/modal/modal.component";

@Component({
    selector: 'app-job-list',
    templateUrl: './job-list.component.html',
    styleUrls: ['./job-list.component.scss']
})
export class JobListComponent implements OnInit {
    @ViewChild( ModalComponent )
    private modalComponent      :   ModalComponent;

    jobs: any;
    categories: any;
    total: 0;
    number_per_page = 10;
    pager = 1;
    indexElement = (this.pager - 1) * this.number_per_page;
    model = { keyword: '', start_date: 0, end_date: 0, category: 0 };

    constructor(private restfulService: RestfulService) { }

    private myDatePickerOptions: IMyDpOptions = {
        dateFormat: 'yyyy/mm/dd',
    };

    ngOnInit() {
        let data = {
            'page_limit': this.number_per_page,
            'page_number': this.pager
        };
        this.getListJob(data);
        this.getListCategory();
    }

    private getListJob(data: any) {
        let url = 'admin/job/list';
        this.restfulService.doGet(url, data).subscribe(commonResponse => this.handleResponse(commonResponse));
    }
    private getListCategory() {
        let url = 'admin/job/category';
        this.restfulService.doGet(url, null).subscribe(commonResponse => this.handleResponseCategory(commonResponse));
    }

    private handleResponse(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            this.jobs = commonResponse.data.data;
            this.total = commonResponse.data.total_items;
            this.indexElement = (this.pager - 1) * this.number_per_page;
        } else {
            alert(commonResponse.error);
        }
    }

    private handleResponseCategory(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            this.categories = commonResponse.data;
        } else {
            alert(commonResponse.error);
        }
    }

    onStartDateChanged(event: IMyDateModel) {
        this.model.start_date = event.epoc;
    }

    onEndDateChanged(event: IMyDateModel) {
        this.model.end_date = event.epoc;
    }

    search() {
        let data = {
            'page_limit': this.number_per_page,
            'page_number': this.pager,
            'keyword': this.model.keyword,
            'start_date': this.model.start_date,
            'end_date': this.model.end_date,
            'category': this.model.category
        };
        this.getListJob(data);
    }

    pageChanged(event) {
        this.pager = event;
        this.search();
    }

    //----------delete--------------//

    onDelete(id: number) {
        this.modalComponent.setBody("求人を削除します。");
        this.modalComponent.setBody2("よろしければOKボタンを押してください。");
        this.modalComponent.putData("id", id);
        this.modalComponent.show();
    }

    delete() {
        let url = 'admin/job/delete/' + this.modalComponent.getData("id");
        if (this.modalComponent.getData("id"))
            this.restfulService.doPost(url, {}).subscribe(commonResponse => this.handleResponseDelete(commonResponse));
        this.modalComponent.hide();
    }

    private handleResponseDelete(commonResponse: any) {
        if (commonResponse == null) return;
        if (commonResponse.success) {
            this.search();
            this.modalComponent.toast('削除しました。');
        } else {
            alert(commonResponse.error);
        }
    }
}
