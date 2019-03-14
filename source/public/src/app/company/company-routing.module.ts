import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';

import { CompanyAuthGuard } from './shared/company-auth.guard';
import { CompanyPermissionGuard } from './shared/company-permission.guard';

import { CompanyComponent } from './company.component';
import { LoginComponent } from './login/login.component';
import { JobListComponent } from './job/job-list/job-list.component';
import { JobDetailComponent } from './job/job-detail/job-detail.component';
import { JobAddComponent } from './job/job-add/job-add.component';
import { JobEditComponent } from './job/job-edit/job-edit.component';

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

import {MyDatePickerModule} from 'mydatepicker';

const routes: Routes = [
    {
        path: 'company', component: CompanyComponent, canActivate: [CompanyAuthGuard], children: [
            { path: '', redirectTo: 'job', pathMatch: 'full' },
            {
                path: 'job', children: [
                    { path: '', component: JobListComponent },
                    { path: 'add', component: JobAddComponent },
                    { path: ':jobId/detail', component: JobDetailComponent },
                    { path: ':jobId/edit', component: JobEditComponent },
                    { path: ':jobId/applicant', component: ApplicantsComponent },
                    { path: ':jobId/applicant-detail/:applicantId', component: ApplicantDetailComponent },
                    { path: ':jobId/determine/:applicantId', component: ApplicantDetailComponent },
                    { path: ':jobId/recruit/:applicantId', component: RecruitComponent },
                    { path: ':jobId/bonus/:applicantId', component: BonusComponent },
                    { path: ':jobId/report/:applicantId', component: ReportComponent },
                    { path: ':jobId/settle/:applicantId', component: SettleComponent },
                ]
            },
            {
                path: 'staff', canActivate: [CompanyPermissionGuard], children: [
                    { path: '', component: StaffListComponent },
                    { path: 'add', component: StaffAddComponent },
                    { path: 'edit/:id', component: StaffEditComponent }
                ]
            },
            {
                path: 'credit', children: [
                    { path: '', component: CreditListComponent },
                    { path: 'add', component: CreditAddComponent },
                    { path: 'edit/:id', component: CreditEditComponent }
                ]
            },
            { path: 'chat/:id', component: ChatComponent },
            { path: 'settlement', component: SettlementListComponent },
            { path: 'new-applicants', component: NewApplicantsComponent },
            { path: 'info', component: CompanyInfoComponent },
            { path: 'info/edit', canActivate: [CompanyPermissionGuard], component: CompanyEditComponent },
            { path: 'chat', component: ChatListComponent },

        ]
    },
    { path: 'company/login', component: LoginComponent }
];

@NgModule({
    imports: [RouterModule.forChild(routes)],
    exports: [RouterModule, MyDatePickerModule]
})
export class CompanyRoutingModule { }
