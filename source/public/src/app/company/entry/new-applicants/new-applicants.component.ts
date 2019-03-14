import { Component, OnInit, ViewChild } from '@angular/core';
import { Router, ActivatedRoute } from '@angular/router';

import { DataShareService, MoveFrom } from '../../shared/services/data-share.service';
import { CompanyService } from '../../shared/services/company.service';
import { RestfulService } from '../../../shared/services/restful.service';

import { ModalComponent } from '../../partials/modal/modal.component';
import { ChatComponent } from '../../entry/chat/chat.component';

@Component({
    selector: 'app-new-applicants',
    templateUrl: './new-applicants.component.html',
    styleUrls: ['./new-applicants.component.scss']
})
export class NewApplicantsComponent implements OnInit {

    constructor(private dataShareService: DataShareService, private companyService: CompanyService, private restfulService: RestfulService, private router: Router, private route: ActivatedRoute) { }

    ngOnInit() {
        this.movedFrom = this.dataShareService.getMovedFrom();
        this.getList();
    }
    ngOnDestroy() {
        this.dataShareService.setMovedFrom(MoveFrom.NewApplicants);
    }
    private listApplicant: any;
    private keyword = "";

    private currentPage = 1;
    private perPage = 10;
    private paginates = [];
    private maxItem: number = 0;
    private indexElement = (this.currentPage - 1) * this.perPage;
    private deleteId: number = 0;
    private jobId : number;

    private movedFrom: MoveFrom;
    private MoveTo: typeof MoveFrom = MoveFrom;

    @ViewChild("modal") modal: ModalComponent;
    @ViewChild("modal_chat") modalChat: ChatComponent;


    getList() {
        let params = {
            'page_limit': this.perPage,
            'page_number': this.currentPage,
            'keyword': this.keyword
        };
        this.restfulService.doGet("company/job/applicant/new", params).subscribe(res => {
            if (res.success) {
                this.listApplicant = res.data.data;
                this.maxItem = res.data.total;
                this.indexElement = (this.currentPage - 1) * this.perPage;
            }
            else {
                this.router.navigate(["/company/job"]);
            }
        });
    }

    search(form) {
        this.keyword = form.keyword;
        this.currentPage = 1;
        this.getList();
    }

    toggleModalDelete(jobId) {
        this.modal.confirm(`応募者を削除します。 <br>
            よろしければOKボタンを押してください`);
        this.deleteId = jobId;
    }

    confirmDelete(confirm: boolean) {
        if (confirm && this.deleteId) {
            this.restfulService.doPost("company/job/applicant/delete/" + this.deleteId, {}).subscribe(res => {
                if (res.success) {
                    if (this.listApplicant.length == 1 && this.currentPage > 1) {
                        this.currentPage = this.currentPage - 1;
                    }
                    this.deleteId = 0;
                    this.modal.toast('削除しました');
                    this.getList();
                }
                else {
                    this.modal.toast(res.error);
                }
            });
        }
    }
    
    pageChanged(event) {
        this.currentPage = event;
        this.getList();
    }

    chat(id){
        this.modalChat.detail(id);
    }

    goDetail(applicant, to) {
        this.dataShareService.setData({
            applicant,
            moveTo: to,
        });
        let url = '';
        switch (to) {
        case this.MoveTo.ApplicantDetail:
            url = `/company/job/${applicant.job_id}/applicant-detail/${applicant.id}`;
            break;
        case this.MoveTo.Determine:
            url = `/company/job/${applicant.job_id}/determine/${applicant.id}`;
            break;
        }
        this.router.navigate([url]);
    }
}
