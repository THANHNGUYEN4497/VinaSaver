import { NgModule } from '@angular/core';
import { Routes, RouterModule } from '@angular/router';
import { ShareComponent } from './shared/component/share/share.component';

const routes: Routes = [
	{ path: '', pathMatch: 'full', redirectTo: '/company/job' },
	{ path: 'share_content', component: ShareComponent }
];

@NgModule({
	imports: [RouterModule.forRoot(routes, { scrollPositionRestoration: 'enabled' })],
	exports: [RouterModule]
})
export class AppRoutingModule { }
