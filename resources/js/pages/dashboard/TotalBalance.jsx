import { usePage } from '@inertiajs/react';

export default function TotalBalance({ className = 'w-full' }) {
    const { props } = usePage();
    const total = Number(props?.summary?.total_balance) || 0;

    return (
        <div className={className}>
            <div className="p-4 relative z-10">
                <div className="flex items-center space-x-4">
                    <div className="circle-icon">
                        <i className="fa-solid fa-wallet" aria-hidden="true"></i>
                    </div>

                    <div>
                        <div className="text-sm text-muted-foreground">Balance</div>
                        <div className="text-2xl font-semibold">{new Intl.NumberFormat('vi-VN').format(total)} VND</div>
                    </div>
                </div>
            </div>
        </div>
    );
}