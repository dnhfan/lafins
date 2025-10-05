function JarBox({ children }) {
    return (
        <div className="h-full p-3 border border-gray-200 rounded-lg bg-white shadow-sm text-center flex items-center justify-center">
            <p className="m-0">{children ?? 'box'}</p>
        </div>
    )
}

export default function JarList({ className }) {
    const containerClass = `${className ?? ''} grid grid-cols-1 sm:grid-cols-3 md:grid-cols-6 gap-4 items-stretch`;

    return (
        <div className={containerClass}>
            <JarBox>Jar 1</JarBox>
            <JarBox>Jar 2</JarBox>
            <JarBox>Jar 3</JarBox>
            <JarBox>Jar 4</JarBox>
            <JarBox>Jar 5</JarBox>
            <JarBox>Jar 6</JarBox>
        </div>
    )
}