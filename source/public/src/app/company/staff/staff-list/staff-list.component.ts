import { Component, OnInit, ViewChild } from '@angular/core';
import { Router } from '@angular/router';

import { environment } from '../../../../environments/environment';
import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';
import { ModalComponent } from '../../partials/modal/modal.component';

@Component({
    selector: 'app-staff-list',
    templateUrl: './staff-list.component.html',
    styleUrls: ['./staff-list.component.scss']
})
export class StaffListComponent implements OnInit {

    private listStaff: any;
    private listPosition: any;

    private currentPage = 1;
    private perPage = 10;
    private paginates = [];
    private maxItem: number = 0;

    private phone_number = "";
    private keyword = "";
    private position = "";

    private currentUserId: number = 0;
    private deleteId: number = 0;
    @ViewChild("modal") modal: ModalComponent;

    constructor(private companyService: CompanyService, private restfulService: RestfulService, private router: Router) { }

    ngOnInit() {
        this.currentUserId = parseInt(this.companyService.getId());
        this.getListStaff();
        this.getListPosition();
    }

    getListStaff() {
        let params = {
            'page_limit': this.perPage,
            'page_number': this.currentPage,
            'keyword': this.keyword,
            'phone_number': this.phone_number,
            'position': this.position
        };
        this.restfulService.doGet("company/staff/list", params).subscribe(res => {
            if (res.success) {
                this.listStaff = res.data.data;
                this.maxItem = res.data.total;
            }
            else {
                console.log(res.error);
            }
        });
    }

    search(form) {
        this.keyword = form.keyword;
        this.phone_number = form.phone_number;
        this.position = form.position;
        this.currentPage = 1;
        this.getListStaff();
    }

    toggleModalDelete(jobId) {
        this.modal.confirm(`スタッフを削除します。 <br>
            よろしければOKボタンを押してください`);
        this.deleteId = jobId;
    }

    confirmResponse(confirm: boolean) {
        if (confirm && this.deleteId) {
            this.restfulService.doPost('company/staff/delete/' + this.deleteId, {}).subscribe(res => {
                if (res.success) {
                    if (this.listStaff.length == 1 && this.currentPage > 1) {
                        this.currentPage = this.currentPage - 1;
                    }
                    this.getListStaff();
                    this.deleteId = 0;
                    this.modal.toast('削除しました');
                }
                else {
                    console.log(res.error);
                }
            });
        }
    }

    getListPosition() {
        this.restfulService.doGet('company/job/extend/position', null).subscribe(res => {
            if (res.success) {
                this.listPosition = res.data;
            }
            else {
                console.log(res.error);
            }
        });
    }

    pageChanged(event) {
        this.currentPage = event;
        this.getListStaff();
    }
}
