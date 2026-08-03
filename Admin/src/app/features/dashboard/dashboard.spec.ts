import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';

import { ApiEnvelope } from '../../core/auth/auth.models';
import { Dashboard } from './dashboard';
import { DashboardSnapshot } from './dashboard.models';

describe('Dashboard', () => {
  let fixture: ComponentFixture<Dashboard>;
  let http: HttpTestingController;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [Dashboard],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    }).compileComponents();

    http = TestBed.inject(HttpTestingController);
    fixture = TestBed.createComponent(Dashboard);
    fixture.detectChanges();
    http.expectOne((request) => request.url === '/api/admin/v1/dashboard').flush(envelope(snapshot));
    fixture.detectChanges();
  });

  afterEach(() => http.verify());

  it('renders real permission-scoped dashboard metrics', () => {
    const compiled = fixture.nativeElement as HTMLElement;
    expect(fixture.componentInstance).toBeTruthy();
    expect(compiled.querySelectorAll('.tile')).toHaveLength(4);
    expect(compiled.textContent).toContain('12');
    expect(compiled.textContent).toContain('3');
  });
});

const snapshot: DashboardSnapshot = {
  range: { from: '2026-08-01', to: '2026-08-03', timezone: 'Asia/Ho_Chi_Minh' },
  capabilities: { products: true, content: true, leads: true, activity: true, analytics: false, pages: false, top_viewed: false },
  cards: {
    products: { total: 12, published: 8 },
    content: { drafts: 3, scheduled: 1, pages: null },
    leads: { total: 5, new_in_range: 2, overdue_follow_up: 1, by_type: { contact: 2 }, by_status: { new: 2 } },
  },
  charts: { leads: [{ date: '2026-08-03', value: 2 }], published_products: [] },
  recent_activity: [],
  analytics: { enabled: false, top_search_terms: [], top_viewed: [] },
  generated_at: '2026-08-03T00:00:00Z',
  cache_ttl_seconds: 60,
};

function envelope(data: DashboardSnapshot): ApiEnvelope<DashboardSnapshot> {
  return { success: true, data, meta: { request_id: '01KTEST00000000000000000' }, message: null };
}
