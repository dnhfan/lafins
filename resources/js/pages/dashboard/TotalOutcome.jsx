import { usePage } from '@inertiajs/react';

export default function TotalOutcome({ className = 'w-full z-30' }) {
    const { props } = usePage();
    const total = Number(props?.summary?.total_outcome) || 0;

    return (
        <div className={className}>
            <div className="p-4 relative z-40">
                <div className="flex items-center space-x-4">
                    <div className="circle-icon">
                        <i className="fa-solid fa-arrow-up"></i>
                    </div>
                    <div>
                        <div className="text-sm text-muted-foreground">Outcome</div>
                        <div className="text-2xl font-semibold text-red-600">{new Intl.NumberFormat('vi-VN').format(total)} VND</div>
                    </div> 
                </div>    
            </div>
        </div>
    );
}