import { NgModule } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { NgxPaginationModule } from 'ngx-pagination';

import { ChatService } from './shared/services/chat.service';
import { RestfulService } from '../shared/services/restful.service';
import { HttpService } from '../shared/services/http.service';
import { CompanyService } from './shared/services/company.service';

import { CompanyRoutingModule } from './company-routing.module';
import { CompanyAuthGuard } from './shared/company-auth.guard';

import { CompanyComponent } from './company.component';
import { LoginComponent } from './login/login.component';
import { JobListComponent } from './job/job-list/job-list.component';
import { JobAddComponent } from './job/job-add/job-add.component';
import { JobEditComponent } from './job/job-edit/job-edit.component';
import { JobDetailComponent } from './job/job-detail/job-detail.component';
import { StaffListComponent } from './staff/staff-list/staff-list.component';
import { StaffAddComponent } from './staff/staff-add/staff-add.component';
import { StaffEditComponent } from './staff/staff-edit/staff-edit.component';
import { CompanyEditComponent } from './company/company-edit/company-edit.component';
import { CompanyInfoComponent } from './company/company-info/company-info.component';
import { CreditListComponent } from './credit/credit-list/credit-list.component';
import { CreditEditComponent } from './credit/credit-edit/credit-edit.component';
import { CreditAddComponent } from './credit/credit-add/credit-add.component';
import { SettlementListComponent } from './settlement/settlement-list/settlement-list.component';
import { ApplicantsComponent } from './entry/applicants/applicants.component';
import { ChatComponent } from './entry/chat/chat.component';
import { ApplicantDetailComponent } from './entry/applicant-detail/applicant-detail.component';
import { RecruitComponent } from './entry/recruit/recruit.component';
import { BonusComponent } from './entry/bonus/bonus.component';
import { ReportComponent } from './entry/report/report.component';
import { SettleComponent } from './entry/settle/settle.component';
import { NewApplicantsComponent } from './entry/new-applicants/new-applicants.component';
import { ChatListComponent } from './chat/chat-list/chat-list.component';
import { AgmCoreModule } from "@agm/core";
import { ModalComponent } from './partials/modal/modal.component';

@NgModule({
	declarations: [CompanyComponent, LoginComponent, StaffListComponent, JobAddComponent, JobEditComponent, JobDetailComponent, StaffAddComponent, StaffEditComponent, CompanyEditComponent, CompanyInfoComponent, CreditListComponent, CreditEditComponent, CreditAddComponent, SettlementListComponent, ApplicantsComponent, ChatComponent, ApplicantDetailComponent, RecruitComponent, BonusComponent, ReportComponent, SettleComponent, JobListComponent, NewApplicantsComponent, ChatListComponent, ModalComponent],

	imports: [
		CommonModule,
		FormsModule,
		ReactiveFormsModule,
		CompanyRoutingModule,
		NgxPaginationModule,
		AgmCoreModule
	],
	providers: [RestfulService, HttpService, CompanyService, ChatService, CompanyAuthGuard]
})
export class CompanyModule {
}
