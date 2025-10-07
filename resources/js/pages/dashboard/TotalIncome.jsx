import { usePage } from '@inertiajs/react';

export default function TotalIncome({ className = 'w-full' }) {
    const { props } = usePage();
    const total = Number(props?.summary?.total_income) || 0;

    return (
        <div className={className}>
            <div className="p-4 relative z-10">
                <div className='flex items-center space-x-4'>
                    <div className='circle-icon'>
                        <i className="fa-solid fa-arrow-down"></i>
                    </div>

                    <div>
                        <div className="text-sm text-muted-foreground">Income</div>
                        <div className="text-2xl font-semibold text-green-600">{new Intl.NumberFormat('vi-VN').format(total)} VND</div>
                    </div>     
                </div> 
            </div>
        </div>
    );
}